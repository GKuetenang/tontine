<?php

namespace App\Http\Controllers;

use App\Enums\LoanStatus;
use App\Models\Contribution;
use App\Models\InsuranceContribution;
use App\Models\Loan;
use App\Models\Membership;
use App\Models\Tontine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $memberships = $request->user()->memberships()
            ->active()
            ->with(['tontine.activeSession'])
            ->withSum('insuranceContributions', 'amount')
            ->latest('joined_at')
            ->paginate(10)
            ->through(fn (Membership $membership): array => [
                'id' => $membership->id,
                'member_number' => $membership->member_number,
                'joined_at' => $membership->joined_at?->format('Y-m-d\TH:i:s'),
                'insurance_total' => (string) ($membership->insurance_contributions_sum_amount ?? '0'),
                'tontine' => $this->tontine($membership->tontine),
                'active_session' => $membership->tontine->activeSession ? [
                    'name' => $membership->tontine->activeSession->name,
                    'slug' => $membership->tontine->activeSession->slug,
                ] : null,
            ]);

        $membershipIds = $request->user()->memberships()->active()->pluck('id');

        return Inertia::render('account/index', [
            'collection' => $memberships,
            'summary' => [
                'tontines_count' => $membershipIds->count(),
                'insurance_payments_count' => InsuranceContribution::query()->whereIn('membership_id', $membershipIds)->count(),
                'contributions_due_count' => Contribution::query()
                    ->whereHas('sessionParticipant', fn (Builder $query) => $query->whereIn('membership_id', $membershipIds))
                    ->whereDoesntHave('transactions', fn (Builder $query) => $query->credits())
                    ->count(),
                'active_loans_count' => Loan::query()->whereIn('membership_id', $membershipIds)->where('status', LoanStatus::Active)->count(),
            ],
        ]);
    }

    public function insurance(Request $request, ?Tontine $tontine = null): Response
    {
        $membershipIds = $this->membershipIds($request, $tontine);
        $collection = InsuranceContribution::query()
            ->whereIn('membership_id', $membershipIds)
            ->with(['membership.tontine:id,name,slug,currency', 'session:id,name,slug'])
            ->orderFromRequest($request)
            ->paginate(15)
            ->withQueryString()
            ->through(fn (InsuranceContribution $insurance): array => [
                'id' => $insurance->id,
                'amount' => $insurance->amount,
                'occurred_at' => $insurance->occurred_at->format('Y-m-d\TH:i:s'),
                'tontine' => $this->tontine($insurance->membership->tontine),
                'session_name' => $insurance->session->name,
            ]);

        $totals = InsuranceContribution::query()
            ->whereIn('membership_id', $membershipIds)
            ->join('memberships', 'insurance_contributions.membership_id', '=', 'memberships.id')
            ->join('tontines', 'memberships.tontine_id', '=', 'tontines.id')
            ->selectRaw('tontines.currency, SUM(insurance_contributions.amount) as amount')
            ->groupBy('tontines.currency')
            ->get()
            ->map(fn ($total): array => ['currency' => $total->currency, 'amount' => (string) $total->amount]);

        return Inertia::render('account/insurance', compact('collection', 'totals'));
    }

    public function contributions(Request $request): Response
    {
        $collection = Contribution::query()
            ->whereHas('sessionParticipant.membership', fn (Builder $query) => $query->where('user_id', $request->user()->id))
            ->with(['meeting.session.tontine:id,name,slug,currency'])
            ->withSum(['transactions as paid_amount' => fn (Builder $query) => $query->credits()], 'amount')
            ->latest('id')
            ->paginate(15)
            ->through(fn (Contribution $contribution): array => [
                'id' => $contribution->id,
                'amount_due' => (string) $contribution->amount_due,
                'amount_paid' => (string) ($contribution->paid_amount ?? '0'),
                'status_label' => $contribution->status()->label(),
                'meeting_title' => $contribution->meeting->title,
                'scheduled_at' => $contribution->meeting->scheduled_at->format('Y-m-d\TH:i:s'),
                'tontine' => $this->tontine($contribution->meeting->session->tontine),
            ]);

        return Inertia::render('account/contributions', compact('collection'));
    }

    public function loans(Request $request): Response
    {
        $collection = Loan::query()
            ->whereHas('membership', fn (Builder $query) => $query->where('user_id', $request->user()->id))
            ->with(['session.tontine:id,name,slug,currency'])
            ->withSum('repayments', 'amount')
            ->latest('id')
            ->paginate(15)
            ->through(fn (Loan $loan): array => [
                'id' => $loan->id,
                'principal_amount' => $loan->principal_amount,
                'interest_amount' => $loan->interest_amount,
                'total_due' => $loan->total_due,
                'repaid_amount' => (string) ($loan->repayments_sum_amount ?? '0'),
                'due_at' => $loan->due_at->format('Y-m-d'),
                'status_label' => $loan->status->label(),
                'tontine' => $this->tontine($loan->session->tontine),
            ]);

        return Inertia::render('account/loans', compact('collection'));
    }

    private function membershipIds(Request $request, ?Tontine $tontine): mixed
    {
        $query = $request->user()->memberships()->active();

        if ($tontine) {
            abort_unless($tontine->hasActiveMembership($request->user()), 404);
            $query->where('tontine_id', $tontine->id);
        }

        return $query->pluck('id');
    }

    private function tontine(Tontine $tontine): array
    {
        return ['id' => $tontine->id, 'name' => $tontine->name, 'slug' => $tontine->slug, 'currency' => $tontine->currency];
    }
}
