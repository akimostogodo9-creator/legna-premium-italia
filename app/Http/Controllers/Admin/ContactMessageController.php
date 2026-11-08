<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContactStatus;
use App\Enums\ContactSubject;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\Contact\ContactService;
use App\Services\Settings\SettingsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

// F-AMSG-01→03 — sprint 9
class ContactMessageController extends Controller
{
    public function __construct(private readonly ContactService $contact) {}

    public function index(Request $request, SettingsService $settings): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(ContactStatus::class)],
            'subject' => ['nullable', Rule::enum(ContactSubject::class)],
        ]);

        $messages = ContactMessage::query()
            ->when($filters['q'] ?? null, function (Builder $query, string $q) {
                $like = '%'.$q.'%';
                $query->where(fn (Builder $w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('message', 'like', $like));
            })
            ->when($filters['status'] ?? null, fn (Builder $q, string $s) => $q->where('status', $s))
            ->when($filters['subject'] ?? null, fn (Builder $q, string $s) => $q->where('subject', $s))
            ->latest()
            ->latest('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (ContactMessage $m) => [
                'id' => $m->id,
                'name' => $m->name,
                'email' => $m->email,
                'subject' => $m->subject->label(),
                'excerpt' => str($m->message)->limit(90)->toString(),
                'status' => $m->status->value,
                'status_label' => $m->status->label(),
                'received' => $m->created_at->format('d/m/Y H:i'),
            ]);

        return Inertia::render('admin/messages/index', [
            'messages' => [
                'data' => $messages->items(),
                'prev_url' => $messages->previousPageUrl(),
                'next_url' => $messages->nextPageUrl(),
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
            ],
            'filters' => $filters,
            'statuses' => collect(ContactStatus::cases())->map(fn (ContactStatus $s) => ['value' => $s->value, 'label' => $s->label()]),
            'subjects' => collect(ContactSubject::cases())->map(fn (ContactSubject $s) => ['value' => $s->value, 'label' => $s->label()]),
            'retentionMonths' => (int) $settings->get('contact_retention_months', 24),
        ]);
    }

    public function show(ContactMessage $message): Response
    {
        $this->contact->markRead($message); // F-AMSG-02 : ouvrir = lu
        $message->load('handler:id,name');

        return Inertia::render('admin/messages/show', [
            'message' => [
                'id' => $message->id,
                'name' => $message->name,
                'email' => $message->email,
                'phone' => $message->phone,
                'subject' => $message->subject->label(),
                'message' => $message->message, // texte brut : React l'échappe (étape 7, XSS)
                'status' => $message->status->value,
                'status_label' => $message->status->label(),
                'received' => $message->created_at->format('d/m/Y H:i'),
                'handled_by' => $message->handler?->name,
                'handled_at' => $message->handled_at?->format('d/m/Y H:i'),
                'reply_subject' => __('admin.messages_admin.reply_subject', ['site' => config('app.name')]),
            ],
        ]);
    }

    public function toggleHandled(Request $request, ContactMessage $message): RedirectResponse
    {
        $message->status === ContactStatus::Handled
            ? $this->contact->reopen($message)
            : $this->contact->markHandled($message, $request->user());

        return back()->with('success', __('admin.messages.saved'));
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return to_route('admin.messages.index')->with('success', __('admin.messages.deleted'));
    }

    public function destroyMany(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array', 'max:100'], 'ids.*' => ['integer']])['ids'];

        ContactMessage::whereKey($ids)->delete();

        return back()->with('success', __('admin.messages.deleted'));
    }
}
