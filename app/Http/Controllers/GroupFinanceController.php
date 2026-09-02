<?php

namespace App\Http\Controllers;

use App\Actions\Finances\BuildGroupFinancialDashboardAction;
use App\Enums\GroupPermission;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class GroupFinanceController extends Controller
{
    public function index(Request $request, Group $group, BuildGroupFinancialDashboardAction $action): Response
    {
        Gate::authorize(GroupPermission::ViewAccounting->value);

        $filters = $request->validate([
            'session_id' => [
                'nullable',
                'integer',
                Rule::exists('group_sessions', 'id')->where('group_id', $group->id),
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
            'group' => ['id' => $group->id, 'name' => $group->name, 'slug' => $group->slug, 'currency' => $group->currency],
            'dashboard' => $action->execute($group, $filters),
            'filters' => $filters,
            'sessions' => $group->sessions()
                ->with(['meetings' => fn ($query) => $query->orderBy('number')->select(['id', 'session_id', 'number', 'title', 'scheduled_at'])])
                ->orderByDesc('start_at')
                ->get(['id', 'name'])
                ->map(fn ($session): array => [
                    'value' => (string) $session->id,
                    'label' => $session->name,
                    'meetings' => $session->meetings->map(fn ($meeting): array => [
                        'value' => (string) $meeting->id,
                        'label' => __('Assise n°:number — :title', [
                            'number' => $meeting->number,
                            'title' => $meeting->title,
                        ]),
                    ]),
                ]),
        ]);
    }
}
