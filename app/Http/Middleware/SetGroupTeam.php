<?php

namespace App\Http\Middleware;

use App\Models\Group;
use App\Support\GroupPermissionChecker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetGroupTeam
{
    public function __construct(private GroupPermissionChecker $permissions) {}

    public function handle(Request $request, Closure $next): Response
    {
        $group = $request->route('group');

        abort_unless($group instanceof Group, 404);

        $user = $request->user();
        abort_unless($user, 401);
        $this->permissions->prepare($user, $group);

        return $next($request);
    }
}
