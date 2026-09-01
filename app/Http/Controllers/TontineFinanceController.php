<?php

namespace App\Http\Controllers;

use App\Actions\Finances\BuildTontineFinancialDashboardAction;
use App\Enums\TontinePermission;
use App\Models\Tontine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TontineFinanceController extends Controller
{
    public function index(Request $request, Tontine $tontine, BuildTontineFinancialDashboardAction $action): Response
    {
        Gate::authorize(TontinePermission::ViewAccounting->value);

        $filters = $request->validate([
            'session_id' => [
                'nullable',
                'integer',
                Rule::exists('tontine_sessions', 'id')->where('tontine_id', $tontine->id),
            ],
            'meeting_id' => [
                'nullable',
                'integer',
                'required_with:session_id',
                Rule::exists('meetings', 'id')->where(
                    fn ($query) => $query
                        ->where('session_id', $request->integer('session_id'))
                        ->whereNull('deleted_at'),
                ),
            ],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return Inertia::render('finances/index', [
            'tontine' => ['id' => $tontine->id, 'name' => $tontine->name, 'slug' => $tontine->slug, 'currency' => $tontine->currency],
            'dashboard' => $action->execute($tontine, $filters),
            'filters' => $filters,
            'sessions' => $tontine->sessions()
                ->with(['meetings' => fn ($query) => $query->orderBy('number')->select(['id', 'session_id', 'number', 'title', 'scheduled_at'])])
                ->orderByDesc('start_at')
                ->get(['id', 'name'])
                ->map(fn ($session): array => [
                    'value' => (string) $session->id,
                    'label' => $session->name,
                    'meetings' => $session->meetings->map(fn ($meeting): array => [
                        'value' => (string) $meeting->id,
                        'label' => __('Réunion n°:number — :title', [
                            'number' => $meeting->number,
                            'title' => $meeting->title,
                        ]),
                    ]),
                ]),
        ]);
    }
}
