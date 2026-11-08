<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Content\ContentRepository;
use Inertia\Inertia;
use Inertia\Response;

/**
 * F-PAGE-01 (Chi siamo) et F-PAGE-02 (Consegna) — sprint 8.
 * R-LIV-03 : un bloc sans texte n'est pas envoyé (rien n'est inventé — CDC §14, §16).
 */
class ContentPageController extends Controller
{
    public function __construct(private readonly ContentRepository $content) {}

    public function about(): Response
    {
        return $this->render('chi_siamo', 'public/about');
    }

    public function delivery(): Response
    {
        return $this->render('consegna', 'public/delivery');
    }

    private function render(string $page, string $component): Response
    {
        $sections = $this->content->page($page);
        $intro = $sections['intro'] ?? null;
        unset($sections['intro']);

        // Blocs « titre + texte » : seulement ceux dont le texte est rempli
        $blocks = collect($sections)
            ->filter(fn (array $block) => filled($block['body'] ?? null))
            ->map(fn (array $block, string $key) => ['key' => $key, ...$block])
            ->values()
            ->all();

        return Inertia::render($component, [
            'intro' => $intro,
            'blocks' => $blocks,
        ]);
    }
}
