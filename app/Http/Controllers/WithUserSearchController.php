<?php

namespace App\Http\Controllers;

use App\Enums\MembershipStatus;
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
}
