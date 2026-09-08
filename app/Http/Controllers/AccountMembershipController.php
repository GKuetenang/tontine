<?php

namespace App\Http\Controllers;

use App\Actions\Memberships\LeaveMembershipAction;
use App\Models\Membership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountMembershipController extends Controller
{
    public function destroy(Request $request, Membership $membership, LeaveMembershipAction $action): RedirectResponse
    {
        abort_unless($membership->user_id === $request->user()->id, 404);

        $action->execute($membership);

        Inertia::flash('success', __('Vous avez quitté la réunion avec succès.'));

        return to_route('account.index');
    }
}
