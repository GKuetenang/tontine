<?php

namespace App\Http\Middleware;

use App\Models\Tontine;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;

class SetPermissionsTeamId
{
    public function handle(Request $request, Closure $next): mixed
    {
        $tontine = $request->route('tontine');

        if (! $tontine instanceof Tontine) {
            return $next($request);
        }

        $user = $request->user();

        abort_unless($user, 403);
        abort_unless(
            $user->id === $tontine->user_id
            || $tontine->memberships()->active()->where('user_id', $user->id)->exists(),
            403
        );

        $registrar = app(PermissionRegistrar::class);
        $previousTeamId = $registrar->getPermissionsTeamId();
        $registrar->setPermissionsTeamId($tontine->id);

        try {
            return $next($request);
        } finally {
            $registrar->setPermissionsTeamId($previousTeamId);
        }
    }
}
