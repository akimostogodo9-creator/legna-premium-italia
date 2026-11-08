<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\Content\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * F-ALEG-01 / R-LEG-01→02 (sprint 8) : les 5 pages sont créées par le seeder ; l'admin les édite et les publie.
 * Une page vide ne peut pas être publiée. Le pied de page est mis à jour immédiatement.
 */
class LegalPageController extends Controller
{
    public function __construct(private readonly HtmlSanitizer $sanitizer) {}

    public function index(): Response
    {
        return Inertia::render('admin/legal/index', [
            'pages' => Page::orderBy('id')->get()->map(fn (Page $p) => [
                'id' => $p->id,
                'slug' => $p->slug,
                'title' => $p->title,
                'is_published' => $p->is_published,
                'is_empty' => blank($p->content),
                'updated_at' => $p->content_updated_at?->format('d/m/Y'),
            ]),
        ]);
    }

    public function edit(Page $page): Response
    {
        return Inertia::render('admin/legal/form', [
            'page' => $page->only(['id', 'slug', 'title', 'content', 'is_published', 'meta_description']),
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'content' => ['nullable', 'string', 'max:100000'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'is_published' => ['boolean'],
        ]);

        $content = $this->sanitizer->clean($data['content'] ?? null);
        $publish = (bool) ($data['is_published'] ?? false);

        if ($publish && $content === null) {
            throw ValidationException::withMessages(['is_published' => __('admin.legal.cannot_publish_empty')]);
        }

        $page->fill([
            'title' => $data['title'],
            'content' => $content,
            'meta_description' => $data['meta_description'] ?? null,
            'is_published' => $publish,
        ]);

        // « Ultimo aggiornamento » : seulement quand le TEXTE change
        if ($page->isDirty('content')) {
            $page->content_updated_at = now();
        }

        $page->save();
        Cache::forget('legna.legal_pages'); // pied de page (sprint 3)

        return to_route('admin.legal.index')->with('success', __('admin.messages.saved'));
    }
}
