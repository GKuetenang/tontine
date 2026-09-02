<?php

namespace App\Actions\Dashboard;

use App\Enums\LoanStatus;
use App\Models\Contribution;
use App\Models\Loan;
use App\Models\Meeting;
use App\Models\Tontine;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class BuildUserDashboardAction
{
    public function execute(User $user): array
    {
        $tontines = Tontine::query()
            ->accessibleBy($user)->active()
            ->with(['activeSession:id,tontine_id,name,slug'])
            ->withCount(['memberships as active_members_count' => fn (Builder $query) => $query->active()])
            ->latest('updated_at')
            ->get(['id', 'name', 'slug', 'currency']);

        $upcomingMeetings = Meeting::query()
            ->where('scheduled_at', '>=', now())
            ->whereHas('session.participants.membership', fn (Builder $query) => $query->where('user_id', $user->id))
            ->with(['session:id,tontine_id,name,slug', 'session.tontine:id,name,slug,currency'])
            ->orderBy('scheduled_at')->limit(5)->get();

        $contributions = Contribution::query()
            ->whereHas('sessionParticipant.membership', fn (Builder $query) => $query->where('user_id', $user->id))
            ->with(['meeting.session.tontine:id,name,slug,currency'])
            ->withSum(['transactions as paid_amount' => fn (Builder $query) => $query->credits()], 'amount')
            ->get();

        $loans = Loan::query()
            ->where('status', LoanStatus::Active)
            ->whereHas('membership', fn (Builder $query) => $query->where('user_id', $user->id))
            ->with(['session.tontine:id,name,slug,currency'])
            ->withSum('repayments', 'amount')->get();

        $recentTransactions = Transaction::query()
            ->whereHas('membership', fn (Builder $query) => $query->where('user_id', $user->id))
            ->with(['session:id,tontine_id,name,slug', 'session.tontine:id,name,slug,currency'])
            ->latest('occurred_at')->limit(8)->get();

        return [
            'has_tontines' => $tontines->isNotEmpty(),
            'summary' => [
                'tontines_count' => $tontines->count(),
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
                'tontine_name' => $meeting->session->tontine->name,
                'tontine_slug' => $meeting->session->tontine->slug,
                'session_slug' => $meeting->session->slug,
                'meeting_slug' => $meeting->slug,
            ])->all(),
            'recent_transactions' => $recentTransactions->map(fn (Transaction $transaction): array => [
                'id' => $transaction->id,
                'type_label' => $transaction->type->label(),
                'direction' => $transaction->direction->value,
                'amount' => $transaction->amount,
                'occurred_at' => $transaction->occurred_at->format('Y-m-d\TH:i:s'),
                'tontine_name' => $transaction->session->tontine->name,
                'currency' => $transaction->session->tontine->currency,
            ])->all(),
            'tontines' => $tontines->map(fn (Tontine $tontine): array => [
                'id' => $tontine->id,
                'name' => $tontine->name,
                'slug' => $tontine->slug,
                'currency' => $tontine->currency,
                'active_members_count' => $tontine->active_members_count,
                'active_session' => $tontine->activeSession ? [
                    'name' => $tontine->activeSession->name,
                    'slug' => $tontine->activeSession->slug,
                ] : null,
            ])->all(),
        ];
    }

    private function contributionsByCurrency(Collection $contributions): array
    {
        return $contributions
            ->groupBy(fn (Contribution $contribution): string => $contribution->meeting->session->tontine->currency)
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
            ->groupBy(fn (Loan $loan): string => $loan->session->tontine->currency)
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
