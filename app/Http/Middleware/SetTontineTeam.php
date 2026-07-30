<?php

namespace App\Http\Middleware;

use App\Models\Tontine;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTontineTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        $tontine = $request->route('tontine');

        abort_unless($tontine instanceof Tontine, 404);

        $user = $request->user();
        abort_unless($user, 401);
        setPermissionsTeamId($tontine->id);

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        return $next($request);
    }
}
