<?php

namespace App\Services\Contact;

use App\Enums\ContactStatus;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\User;
use App\Services\Settings\SettingsService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * R-CONT-03→07 (sprint 9) : enregistrement + e-mail au vendeur, anti-spam, limite par visiteur.
 * Aucune adresse IP n'est enregistrée en base : la limite utilise le cache (empreinte de l'IP, expire après 1 h).
 */
class ContactService
{
    public function __construct(private readonly SettingsService $settings) {}

    /** Le formulaire est proposé seulement s'il est activé ET qu'une adresse de réception existe. */
    public function isAvailable(): bool
    {
        return (bool) $this->settings->get('contact_form_enabled', true) && $this->settings->filled('contact_email');
    }

    /**
     * @param  array{name: string, email: string, phone: ?string, subject: string, message: string}  $data
     * @return ContactMessage|null null si le message a été ignoré (piège anti-robot)
     */
    public function submit(array $data, string $ip, bool $spamTrap): ?ContactMessage
    {
        // Robot : on fait comme si tout allait bien, sans rien enregistrer ni envoyer
        if ($spamTrap) {
            return null;
        }

        $key = 'contact:'.sha1($ip);
        $max = max(1, (int) $this->settings->get('contact_rate_limit_per_hour', 5));

        // Ajustement du contrat (dossier) : erreur de validation claire plutôt qu'une page 429
        if (RateLimiter::tooManyAttempts($key, $max)) {
            throw ValidationException::withMessages(['message' => __('public.contact.too_many')]);
        }

        RateLimiter::hit($key, 3600);

        $message = ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'privacy_accepted_at' => now(),
            'status' => ContactStatus::New,
        ]);

        Mail::to($this->settings->get('contact_email'))->queue(new ContactMessageReceived($message));

        return $message;
    }

    public function markRead(ContactMessage $message): void
    {
        if ($message->status === ContactStatus::New) {
            $message->update(['status' => ContactStatus::Read]);
        }
    }

    public function markHandled(ContactMessage $message, User $admin): void
    {
        $message->update([
            'status' => ContactStatus::Handled,
            'handled_by' => $admin->id,
            'handled_at' => now(),
        ]);
    }

    public function reopen(ContactMessage $message): void
    {
        $message->update(['status' => ContactStatus::Read, 'handled_by' => null, 'handled_at' => null]);
    }

    /** R-CONT-06 / F-SYS-01 : supprime les messages plus anciens que la durée de conservation. */
    public function purgeOld(): int
    {
        $months = max(1, (int) $this->settings->get('contact_retention_months', 24));

        return ContactMessage::where('created_at', '<', now()->subMonths($months))->delete();
    }
}
