<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\BuildUserDashboardAction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, BuildUserDashboardAction $action): Response
    {
        return Inertia::render('dashboard', $action->execute($request->user()));
    }
}
