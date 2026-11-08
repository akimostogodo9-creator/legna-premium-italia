<?php

namespace App\Http\Controllers\Public;

use App\Enums\ContactSubject;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\ContactRequest;
use App\Services\Contact\ContactService;
use App\Services\Content\ContentRepository;
use App\Services\Settings\SettingsService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

// F-CONT-01→07 — sprint 9
class ContactController extends Controller
{
    public function __construct(private readonly ContactService $contact) {}

    public function show(ContentRepository $content, SettingsService $settings): Response
    {
        return Inertia::render('public/contact', [
            'intro' => $content->page('contatti')['intro'] ?? null,
            'address' => $settings->get('company_address'),
            'deliveryArea' => $settings->get('delivery_area'),
            'formEnabled' => $this->contact->isAvailable(),
            'subjects' => collect(ContactSubject::cases())
                ->map(fn (ContactSubject $s) => ['value' => $s->value, 'label' => $s->label()]),
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        abort_unless($this->contact->isAvailable(), 404);

        $this->contact->submit(
            $request->safe()->only(['name', 'email', 'phone', 'subject', 'message']),
            (string) $request->ip(),
            $request->isSpamTrap(),
        );

        return back()->with('success', __('public.contact.sent'));
    }
}
