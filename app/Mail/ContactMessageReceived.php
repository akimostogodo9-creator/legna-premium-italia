<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// F-CONT-05 (sprint 9) : copie du message envoyée au vendeur ; « Rispondi » écrit directement au visiteur
class ContactMessageReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->contactMessage->email, $this->contactMessage->name)],
            subject: 'Nuova richiesta dal sito: '.$this->contactMessage->subject->label(),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-message', with: [
            'msg' => $this->contactMessage,
            'adminUrl' => route('admin.messages.show', $this->contactMessage->id),
        ]);
    }
}
