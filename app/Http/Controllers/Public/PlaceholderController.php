<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

// Pages publiques des sprints suivants (évite des liens morts dans le menu) — sprint 3
class PlaceholderController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('public/placeholder');
    }
}
