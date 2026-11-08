<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Page d'accueil (F-HOME). Version provisoire du sprint 0 :
     * le contenu réel arrive aux sprints 3 et 7.
     */
    public function __invoke(): Response
    {
        return Inertia::render('public/home', [
            'title' => __('public.placeholder.title'),
            'text' => __('public.placeholder.text'),
        ]);
    }
}
