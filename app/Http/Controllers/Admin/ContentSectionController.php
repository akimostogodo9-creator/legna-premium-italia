<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContentSectionRequest;
use App\Models\ContentSection;
use App\Services\Content\ContentSectionRegistry;
use App\Services\Content\ContentSectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

// F-ACONT-01→04 — sprint 7
class ContentSectionController extends Controller
{
    public function __construct(
        private readonly ContentSectionRegistry $registry,
        private readonly ContentSectionService $sections,
    ) {}

    public function index(): Response
    {
        $all = ContentSection::query()->orderBy('page')->orderBy('sort_order')->get();

        return Inertia::render('admin/content/index', [
            'pages' => collect($this->registry->pages())->map(fn (string $page) => [
                'key' => $page,
                'sections' => $all->where('page', $page)->values()->map(fn (ContentSection $s) => [
                    'id' => $s->id,
                    'key' => $s->key,
                    'label' => $this->registry->get($s->page, $s->key)['label'],
                    'title' => $s->content['title'] ?? null,
                    'is_visible' => $s->is_visible,
                ]),
            ]),
        ]);
    }

    public function edit(ContentSection $section): Response
    {
        $definition = $this->registry->get($section->page, $section->key);
        $content = $section->content ?? [];

        // URL d'aperçu pour les images (le contenu garde le chemin côté serveur)
        $previews = collect($definition['fields'])
            ->filter(fn (string $type) => $type === 'image')
            ->mapWithKeys(fn ($type, string $field) => [
                $field => ! empty($content[$field]) ? Storage::disk('public')->url($content[$field]) : null,
            ]);

        return Inertia::render('admin/content/edit', [
            'section' => [
                'id' => $section->id,
                'page' => $section->page,
                'key' => $section->key,
                'label' => $definition['label'],
                'is_visible' => $section->is_visible,
            ],
            'fields' => $definition['fields'],
            'required' => $definition['required'],
            'content' => $content,
            'previews' => $previews,
            'icons' => ContentSectionService::ITEM_ICONS,
        ]);
    }

    public function update(ContentSectionRequest $request, ContentSection $section): RedirectResponse
    {
        $this->sections->update(
            $section,
            $request->validated(),
            $request->imageFiles(),
            $request->input('remove_images', []),
        );

        return to_route('admin.content.index')->with('success', __('admin.messages.saved'));
    }

    public function toggleVisibility(ContentSection $section): RedirectResponse
    {
        $this->sections->toggleVisibility($section);

        return back()->with('success', __('admin.messages.saved'));
    }
}
