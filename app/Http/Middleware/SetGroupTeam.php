<?php

namespace App\Http\Middleware;

use App\Models\Group;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetGroupTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        $group = $request->route('group');

        abort_unless($group instanceof Group, 404);

        $user = $request->user();
        abort_unless($user, 401);
        setPermissionsTeamId($group->id);

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        return $next($request);
    }
}
