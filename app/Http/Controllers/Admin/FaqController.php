<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Models\Faq;
use App\Services\Content\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

// F-AFAQ-01 — sprint 8
class FaqController extends Controller
{
    private const PLACEHOLDER = '[DA COMPLETARE]';

    public function __construct(private readonly HtmlSanitizer $sanitizer) {}

    public function index(): Response
    {
        return Inertia::render('admin/faq/index', [
            'faqs' => Faq::ordered()->get(['id', 'question', 'topic', 'is_active', 'show_on_home', 'answer'])
                ->map(fn (Faq $f) => [
                    ...$f->only(['id', 'question', 'topic', 'is_active', 'show_on_home']),
                    // Signale les réponses provisoires créées par le seeder (sprint 1)
                    'is_placeholder' => str_contains((string) $f->answer, self::PLACEHOLDER),
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/faq/form', ['faq' => null, 'topics' => $this->topics()]);
    }

    public function store(FaqRequest $request): RedirectResponse
    {
        Faq::create([...$this->data($request), 'sort_order' => (int) Faq::max('sort_order') + 1]);

        return to_route('admin.faq.index')->with('success', __('admin.messages.saved'));
    }

    public function edit(Faq $faq): Response
    {
        return Inertia::render('admin/faq/form', [
            'faq' => $faq->only(['id', 'question', 'answer', 'topic', 'show_on_home', 'is_active']),
            'topics' => $this->topics(),
        ]);
    }

    public function update(FaqRequest $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->data($request));

        return to_route('admin.faq.index')->with('success', __('admin.messages.saved'));
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return back()->with('success', __('admin.messages.deleted'));
    }

    public function reorder(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:faqs,id']])['ids'];

        DB::transaction(function () use ($ids) {
            foreach (array_values($ids) as $position => $id) {
                Faq::whereKey($id)->update(['sort_order' => $position]);
            }
        });

        return back()->with('success', __('admin.catalog.order_saved'));
    }

    /** @return array<string, mixed> */
    private function data(FaqRequest $request): array
    {
        $data = $request->validated();
        $data['answer'] = $this->sanitizer->clean($data['answer']);
        $data['topic'] = filled($data['topic'] ?? null) ? trim($data['topic']) : null;

        if ($data['answer'] === null) {
            throw ValidationException::withMessages(['answer' => __('validation.required', ['attribute' => __('admin.faq.answer')])]);
        }

        // Une réponse provisoire ne doit jamais être publiée
        if ($data['is_active'] && str_contains($data['answer'], self::PLACEHOLDER)) {
            throw ValidationException::withMessages(['answer' => __('admin.faq.placeholder_warning')]);
        }

        return $data;
    }

    /**
     * Thèmes déjà utilisés (suggestions dans le formulaire).
     *
     * @return list<string>
     */
    private function topics(): array
    {
        return Faq::query()->whereNotNull('topic')->distinct()->orderBy('topic')->pluck('topic')->all();
    }
}
