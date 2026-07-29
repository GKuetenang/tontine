<?php

namespace App\Http\Controllers;

use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Memberships\DeactivateMembershipAction;
use App\Http\Requests\StoreMembershipRequest;
use App\Models\Membership;
use App\Models\Tontine;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function store(StoreMembershipRequest $request, Tontine $tontine, CreateMembershipAction $createMembershipAction): RedirectResponse
    {
        $this->authorize('create', [Membership::class, $tontine]);

        $validated = $request->validated();

        $user = User::query()->findOrFail($validated['user_id']);

        $createMembershipAction->execute(
            tontine: $tontine,
            user: $user,
            roleName: $validated['roleName'],
            invitedBy: array_key_exists('invited_by', $validated)
                ? User::query()->find($validated['invited_by'])
                : null,
        );

        return back()->with('success', 'Membership created.');
    }

    public function destroy(Request $request, Tontine $tontine, Membership $membership, DeactivateMembershipAction $deactivateMembershipAction): RedirectResponse
    {
        $this->authorize('delete', $membership);

        $deactivateMembershipAction->execute($membership);

        return back()->with('success', 'Membership deactivated.');
    }
}
