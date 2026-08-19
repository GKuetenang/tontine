<?php

namespace App\Http\Controllers;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\Session;
use App\Models\Tontine;
use App\Models\User;
use Illuminate\Http\Request;
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
            ->select(['id', 'name', 'email'])
            ->where(function ($query) use ($searchQuery): void {
                $query
                    ->where('name', 'like', "%{$searchQuery}%")
                    ->orWhere('email', 'like', "%{$searchQuery}%");
            })
            ->whereDoesntHave(
                'memberships',
                fn($query) => $query
                    ->where('tontine_id', \request('tontine')->id),
            )
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    protected function usersInTontine(): Collection
    {
        $searchQuery = \request('q_search');

        if (mb_strlen($searchQuery) < 2) {
            return [];
        }

        $tontine = \request('tontine');
        $session = \request('session');

        return User::query()
            ->select(['id', 'name', 'email'])
            ->where(function ($query) use ($searchQuery): void {
                $query
                    ->where('name', 'like', "%{$searchQuery}%")
                    ->orWhere('email', 'like', "%{$searchQuery}%");
            })
            ->whereHas(
                'memberships',
                fn($query) => $query
                    ->where('tontine_id', $tontine->id)
                    ->where('status', MembershipStatus::Active)
            )
            ->whereDoesntHave(
                'memberships.sessionParticipations',
                fn($query) => $query
                    ->where('session_id', $session->id),
            )
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    protected function membershipsInTontine(): Collection
    {
        $searchQuery = request('q_search');

        if (
            ! is_string($searchQuery)
            || mb_strlen($searchQuery) < 2
        ) {
            return collect();
        }

        /** @var Tontine $tontine */
        $tontine = request('tontine');

        /** @var Session $session */
        $session = request('session');

        return Membership::query()
            ->select([
                'id',
                'user_id',
                'member_number',
            ])
            ->with([
                'user:id,name,email',
            ])
            ->where('tontine_id', $tontine->id)
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
                            'email',
                            'like',
                            "%{$searchQuery}%",
                        );
                },
            )
            ->whereDoesntHave(
                'sessionParticipations',
                fn($query) => $query->where(
                    'session_id',
                    $session->id,
                ),
            )
            ->orderBy('id')
            ->limit(10)
            ->get()
            ->map(
                fn(Membership $membership): array => [
                    'id' => $membership->id,
                    'name' => $membership->user->name,
                    'email' => $membership->user->email,
                    'member_number' =>
                    $membership->member_number,
                ],
            );
    }
}
