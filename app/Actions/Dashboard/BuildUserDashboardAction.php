<?php

namespace App\Actions\Dashboard;

use App\Enums\LoanStatus;
use App\Models\Contribution;
use App\Models\Group;
use App\Models\Loan;
use App\Models\Meeting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class BuildUserDashboardAction
{
    public function execute(User $user): array
    {
        $groups = Group::query()
            ->accessibleBy($user)->active()
            ->with(['activeSession:id,group_id,name,slug'])
            ->withCount(['memberships as active_members_count' => fn (Builder $query) => $query->active()])
            ->latest('updated_at')
            ->get(['id', 'name', 'slug', 'currency']);

        $upcomingMeetings = Meeting::query()
            ->where('scheduled_at', '>=', now())
            ->whereHas('session.participants.membership', fn (Builder $query) => $query->where('user_id', $user->id))
            ->with(['session:id,group_id,name,slug', 'session.group:id,name,slug,currency'])
            ->orderBy('scheduled_at')->limit(5)->get();

        $contributions = Contribution::query()
            ->whereHas('sessionParticipant.membership', fn (Builder $query) => $query->where('user_id', $user->id))
            ->with(['meeting.session.group:id,name,slug,currency'])
            ->withSum(['transactions as paid_amount' => fn (Builder $query) => $query->credits()], 'amount')
            ->get();

        $loans = Loan::query()
            ->where('status', LoanStatus::Active)
            ->whereHas('membership', fn (Builder $query) => $query->where('user_id', $user->id))
            ->with(['session.group:id,name,slug,currency'])
            ->withSum('repayments', 'amount')->get();

        $recentTransactions = Transaction::query()
            ->whereHas('membership', fn (Builder $query) => $query->where('user_id', $user->id))
            ->with(['session:id,group_id,name,slug', 'session.group:id,name,slug,currency'])
            ->latest('occurred_at')->limit(8)->get();

        return [
            'has_groups' => $groups->isNotEmpty(),
            'summary' => [
                'groups_count' => $groups->count(),
                'upcoming_meetings_count' => $upcomingMeetings->count(),
                'contributions_due' => $this->contributionsByCurrency($contributions),
                'active_loans_count' => $loans->count(),
                'loans_due' => $this->loansByCurrency($loans),
            ],
            'next_meetings' => $upcomingMeetings->map(fn (Meeting $meeting): array => [
                'id' => $meeting->id,
                'number' => $meeting->number,
                'title' => $meeting->title,
                'scheduled_at' => $meeting->scheduled_at->format('Y-m-d\TH:i:s'),
                'location' => $meeting->location,
                'group_name' => $meeting->session->group->name,
                'group_slug' => $meeting->session->group->slug,
                'session_slug' => $meeting->session->slug,
                'meeting_slug' => $meeting->slug,
            ])->all(),
            'recent_transactions' => $recentTransactions->map(fn (Transaction $transaction): array => [
                'id' => $transaction->id,
                'type_label' => $transaction->type->label(),
                'direction' => $transaction->direction->value,
                'amount' => $transaction->amount,
                'occurred_at' => $transaction->occurred_at->format('Y-m-d\TH:i:s'),
                'group_name' => $transaction->session->group->name,
                'currency' => $transaction->session->group->currency,
            ])->all(),
            'groups' => $groups->map(fn (Group $group): array => [
                'id' => $group->id,
                'name' => $group->name,
                'slug' => $group->slug,
                'currency' => $group->currency,
                'active_members_count' => $group->active_members_count,
                'active_session' => $group->activeSession ? [
                    'name' => $group->activeSession->name,
                    'slug' => $group->activeSession->slug,
                ] : null,
            ])->all(),
        ];
    }

    private function contributionsByCurrency(Collection $contributions): array
    {
        return $contributions
            ->groupBy(fn (Contribution $contribution): string => $contribution->meeting->session->group->currency)
            ->map(fn (Collection $items, string $currency): array => [
                'currency' => $currency,
                'amount' => $this->fromCents($items->sum(fn (Contribution $contribution): int => max(
                    0,
                    $this->toCents((string) $contribution->amount_due) - $this->toCents((string) ($contribution->paid_amount ?? '0')),
                ))),
            ])->filter(fn (array $item): bool => $item['amount'] !== '0.00')->values()->all();
    }

    private function loansByCurrency(Collection $loans): array
    {
        return $loans
            ->groupBy(fn (Loan $loan): string => $loan->session->group->currency)
            ->map(fn (Collection $items, string $currency): array => [
                'currency' => $currency,
                'amount' => $this->fromCents($items->sum(fn (Loan $loan): int => max(
                    0,
                    $this->toCents($loan->total_due) - $this->toCents((string) ($loan->repayments_sum_amount ?? '0')),
                ))),
            ])->filter(fn (array $item): bool => $item['amount'] !== '0.00')->values()->all();
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function fromCents(int $cents): string
    {
        return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
    }
}
