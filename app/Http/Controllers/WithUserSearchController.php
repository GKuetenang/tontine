<?php

namespace App\Http\Controllers;

use App\Enums\MembershipStatus;
use App\Models\Group;
use App\Models\Membership;
use App\Models\Session;
use App\Models\User;
use Illuminate\Support\Collection;

class WithUserSearchController extends Controller
{
    protected function users(): Collection
    {
        $searchQuery = \request('q_search');

        if (mb_strlen($searchQuery) < 2) {
            return [];
        }

        return User::query()
            ->select(['id', 'first_name', 'name', 'email'])
            ->where(function ($query) use ($searchQuery): void {
                $query
                    ->where('name', 'like', "%{$searchQuery}%")
                    ->orWhere('first_name', 'like', "%{$searchQuery}%")
                    ->orWhere('email', 'like', "%{$searchQuery}%");
            })
            ->whereDoesntHave(
                'memberships',
                fn ($query) => $query
                    ->where('group_id', \request('group')->id),
            )
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(
                fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'member_number' => $user->member_number,
                ],
            );
    }

    protected function usersInGroup(): Collection
    {
        $searchQuery = \request('q_search');

        if (mb_strlen($searchQuery) < 2) {
            return [];
        }

        $group = \request('group');
        $session = \request('session');

        return User::query()
            ->select(['id', 'first_name', 'name', 'email'])
            ->where(function ($query) use ($searchQuery): void {
                $query
                    ->where('name', 'like', "%{$searchQuery}%")
                    ->orWhere('first_name', 'like', "%{$searchQuery}%")
                    ->orWhere('email', 'like', "%{$searchQuery}%");
            })
            ->whereHas(
                'memberships',
                fn ($query) => $query
                    ->where('group_id', $group->id)
                    ->where('status', MembershipStatus::Active)
            )
            ->whereDoesntHave(
                'memberships.sessionParticipations',
                fn ($query) => $query
                    ->where('session_id', $session->id),
            )
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(
                fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'member_number' => $user->member_number,
                ],
            );
    }

    protected function membershipsInGroup(): Collection
    {
        $searchQuery = request('q_search');

        if (
            ! is_string($searchQuery)
            || mb_strlen($searchQuery) < 2
        ) {
            return collect();
        }

        /** @var Group $group */
        $group = request('group');

        /** @var Session $session */
        $session = request('session');

        return Membership::query()
            ->select([
                'id',
                'user_id',
                'member_number',
            ])
            ->with([
                'user:id,first_name,name,email',
            ])
            ->where('group_id', $group->id)
            ->where(
                'status',
                MembershipStatus::Active,
            )
            ->whereHas(
                'user',
                function ($query) use ($searchQuery): void {
                    $query
                        ->where(
                            'name',
                            'like',
                            "%{$searchQuery}%",
                        )
                        ->orWhere(
                            'first_name',
                            'like',
                            "%{$searchQuery}%",
                        )
                        ->orWhere(
                            'email',
                            'like',
                            "%{$searchQuery}%",
                        );
                },
            )
            ->whereDoesntHave(
                'sessionParticipations',
                fn ($query) => $query->where(
                    'session_id',
                    $session->id,
                ),
            )
            ->orderBy('id')
            ->limit(10)
            ->get()
            ->map(
                fn (Membership $membership): array => [
                    'id' => $membership->id,
                    'name' => $membership->user->full_name,
                    'email' => $membership->user->email,
                    'member_number' => $membership->member_number,
                ],
            );
    }

    protected function membershipsInSession(): Collection
    {
        $searchQuery = request('q_search');

        if (! is_string($searchQuery) || mb_strlen($searchQuery) < 2) {
            return collect();
        }

        /** @var Session $session */
        $session = request('session');

        return Membership::query()
            ->select(['memberships.id', 'memberships.user_id', 'memberships.member_number'])
            ->with('user:id,first_name,name,email')
            ->whereHas('sessionParticipations', fn ($query) => $query
                ->where('session_id', $session->id)
                ->active())
            ->whereHas('user', fn ($query) => $query
                ->where('name', 'like', "%{$searchQuery}%")
                ->orWhere('first_name', 'like', "%{$searchQuery}%")
                ->orWhere('email', 'like', "%{$searchQuery}%"))
            ->orderBy('memberships.id')
            ->limit(10)
            ->get()
            ->map(fn (Membership $membership): array => [
                'id' => $membership->id,
                'name' => $membership->user->full_name,
                'email' => $membership->user->email,
                'member_number' => $membership->member_number,
            ]);
    }
}
