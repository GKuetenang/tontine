<?php

namespace App\Actions\Penalties;

use App\Enums\AttendanceStatus;
use App\Enums\PenaltyCalculationType;
use App\Enums\PenaltyGraceUnit;
use App\Enums\PenaltyTrigger;
use App\Enums\TransactionDirection;
use App\Models\Contribution;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\Membership;
use App\Models\Penalty;
use App\Models\PenaltyRule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final class ApplyAutomaticMeetingPenaltiesAction
{
    public function execute(Meeting $meeting, CarbonImmutable $assessedAt): void
    {
        $meeting->loadMissing([
            'attendances.sessionParticipant.membership',
            'contributions.sessionParticipant.membership',
            'contributions.transactions',
            'session.group.penaltyRules',
        ]);

        $rules = $meeting->session->group->penaltyRules
            ->where('is_active', true)
            ->where('is_automatic', true);

        foreach ($rules as $rule) {
            foreach ($this->subjects($meeting, $rule, $assessedAt) as [$membership, $contribution]) {
                Penalty::query()->firstOrCreate(
                    [
                        'penalty_rule_id' => $rule->id,
                        'meeting_id' => $meeting->id,
                        'membership_id' => $membership->id,
                    ],
                    [
                        'contribution_id' => $contribution?->id,
                        'trigger' => $rule->trigger,
                        'amount' => $this->amount($rule, $contribution),
                        'rule_name' => $rule->name,
                        'assessed_at' => $assessedAt,
                    ],
                );
            }
        }
    }

    /** @return Collection<int, array{Membership, Contribution|null}> */
    private function subjects(Meeting $meeting, PenaltyRule $rule, CarbonImmutable $assessedAt): Collection
    {
        return match ($rule->trigger) {
            PenaltyTrigger::MeetingAbsent => $meeting->attendances
                ->where('status', AttendanceStatus::Absent)
                ->map(fn (MeetingAttendance $attendance): array => [
                    $attendance->sessionParticipant->membership,
                    $this->contributionFor($meeting, $attendance),
                ]),
            PenaltyTrigger::MeetingLate => $meeting->attendances
                ->where('status', AttendanceStatus::Late)
                ->filter(fn (MeetingAttendance $attendance): bool => $attendance->checked_in_at === null
                    || $attendance->checked_in_at->isAfter($this->deadline($meeting->scheduled_at, $rule)))
                ->map(fn (MeetingAttendance $attendance): array => [
                    $attendance->sessionParticipant->membership,
                    $this->contributionFor($meeting, $attendance),
                ]),
            PenaltyTrigger::ContributionLate => $assessedAt->isAfter($this->deadline($meeting->scheduled_at, $rule))
                ? $this->unpaidContributions($meeting)
                : collect(),
            PenaltyTrigger::ContributionIncomplete => $this->unpaidContributions($meeting),
            PenaltyTrigger::Manual => collect(),
        };
    }

    /** @return Collection<int, array{Membership, Contribution}> */
    private function unpaidContributions(Meeting $meeting): Collection
    {
        return $meeting->contributions
            ->filter(fn (Contribution $contribution): bool => $this->remainingAmount($contribution) > 0)
            ->map(fn (Contribution $contribution): array => [
                $contribution->sessionParticipant->membership,
                $contribution,
            ]);
    }

    private function remainingAmount(Contribution $contribution): int
    {
        $paid = 0;

        foreach ($contribution->transactions as $transaction) {
            if ($transaction->direction === TransactionDirection::Credit) {
                $paid += (int) $transaction->amount;
            }
        }

        return max(0, $contribution->amount_due - $paid);
    }

    private function deadline(CarbonImmutable $date, PenaltyRule $rule): CarbonImmutable
    {
        $grace = $rule->grace_period ?? 0;

        return $rule->grace_unit === PenaltyGraceUnit::Days
            ? $date->addDays($grace)
            : $date->addMinutes($grace);
    }

    private function contributionFor(Meeting $meeting, MeetingAttendance $attendance): ?Contribution
    {
        return $meeting->contributions->firstWhere(
            'session_participant_id',
            $attendance->session_participant_id,
        );
    }

    private function amount(PenaltyRule $rule, ?Contribution $contribution): string
    {
        if ($rule->calculation_type === PenaltyCalculationType::Fixed) {
            return (string) $rule->value;
        }

        $base = $contribution?->amount_due ?? 0;
        [$whole, $decimal] = array_pad(explode('.', $rule->value, 2), 2, '');
        $basisPoints = ((int) $whole * 100) + (int) str_pad(substr($decimal, 0, 2), 2, '0');
        $amountInCents = intdiv(($base * $basisPoints * 100) + 5000, 10000);

        return sprintf('%d.%02d', intdiv($amountInCents, 100), $amountInCents % 100);
    }
}
