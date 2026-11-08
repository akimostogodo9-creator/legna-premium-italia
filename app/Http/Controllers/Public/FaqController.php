<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

// F-PAGE-03 (sprint 8) : questions actives, groupées par thème (ordre des thèmes = ordre de la 1re question)
class FaqController extends Controller
{
    public function __invoke(): Response
    {
        $groups = Faq::active()
            ->ordered()
            ->get(['id', 'question', 'answer', 'topic'])
            ->groupBy(fn (Faq $faq) => $faq->topic ?: '')
            ->map(fn (Collection $faqs, string $topic) => [
                'topic' => $topic !== '' ? $topic : null,
                'faqs' => $faqs->map(fn (Faq $faq) => $faq->only(['id', 'question', 'answer']))->values(),
            ])
            ->values();

        return Inertia::render('public/faq', ['groups' => $groups]);
    }
}
