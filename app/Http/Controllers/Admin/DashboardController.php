<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ConfigurationChecker;
use App\Services\Admin\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

// F-DASH-01→04 (sprint 10) : remplace le tableau de bord provisoire du sprint 2
class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboard, ConfigurationChecker $checker): Response
    {
        return Inertia::render('admin/dashboard', [
            ...$dashboard->summary(),
            'alerts' => $checker->alerts(),
        ]);
    }
}
