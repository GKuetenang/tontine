<?php

namespace App\Actions\Reports;

use App\Data\MeetingData;
use App\Data\MeetingReportData;
use App\Data\MeetingReportSummaryData;
use App\Enums\AttendanceStatus;
use App\Enums\PayoutStatus;
use App\Enums\TransactionDirection;
use App\Models\Meeting;
use Illuminate\Support\Collection;

final class BuildMeetingReportAction
{
    public function execute(
        Meeting $meeting,
    ): MeetingReportData {
        $meeting->load([
            'agendaItems',
            'attendances.sessionParticipant.membership.user',
            'contributions.sessionParticipant.membership.user',
            'contributions.transactions',
            'payouts.drawEntry.sessionParticipant.membership.user',
            'payouts.creator',
            'notes.agendaItem',
            'notes.creator',
            'decisions.agendaItem',
            'decisions.creator',
        ]);

        $contributionsDue = $meeting->contributions
            ->sum('amount_due');

        $contributionsPaid = $meeting->contributions
            ->flatMap->transactions
            ->where(
                'direction',
                TransactionDirection::Credit,
            )
            ->pluck('amount');

        $paidPayouts = $meeting->payouts
            ->where('status', PayoutStatus::Paid)
            ->pluck('amount');

        return new MeetingReportData(
            meeting: MeetingData::fromModel($meeting),
            summary: new MeetingReportSummaryData(
                attendances_total: $meeting->attendances->count(),
                present_total: $meeting->attendances
                    ->where('status', AttendanceStatus::Present)
                    ->count(),
                late_total: $meeting->attendances
                    ->where('status', AttendanceStatus::Late)
                    ->count(),
                absent_total: $meeting->attendances
                    ->where('status', AttendanceStatus::Absent)
                    ->count(),
                excused_total: $meeting->attendances
                    ->where('status', AttendanceStatus::Excused)
                    ->count(),
                pending_total: $meeting->attendances
                    ->where('status', AttendanceStatus::Pending)
                    ->count(),
                contributions_due: $this->formatMoney(
                    (string) $contributionsDue,
                ),
                contributions_paid: $this->sumMoney(
                    $contributionsPaid,
                ),
                contributions_remaining: $this->subtractMoney(
                    (string) $contributionsDue,
                    $this->sumMoney($contributionsPaid),
                ),
                payouts_paid: $this->sumMoney($paidPayouts),
            ),
        );
    }

    /**
     * @param  Collection<int, int|string>  $amounts
     */
    private function sumMoney(
        Collection $amounts,
    ): string {
        $totalCents = $amounts->sum(
            fn (int|string $amount): int => $this->toCents(
                (string) $amount,
            ),
        );

        return $this->fromCents($totalCents);
    }

    private function subtractMoney(
        string $minuend,
        string $subtrahend,
    ): string {
        return $this->fromCents(max(
            0,
            $this->toCents($minuend)
                - $this->toCents($subtrahend),
        ));
    }

    private function formatMoney(string $amount): string
    {
        return $this->fromCents(
            $this->toCents($amount),
        );
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(
            explode('.', $amount, 2),
            2,
            '0',
        );

        return ((int) $whole * 100)
            + (int) str_pad(
                substr($fraction, 0, 2),
                2,
                '0',
            );
    }

    private function fromCents(int $cents): string
    {
        return sprintf(
            '%d.%02d',
            intdiv($cents, 100),
            $cents % 100,
        );
    }
}
