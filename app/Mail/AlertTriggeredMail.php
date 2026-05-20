<?php

namespace App\Mail;

use App\DTOs\ProfitResult;
use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlertTriggeredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Alert        $alert,
        public readonly ProfitResult $result,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[FFXIV Market] Alerta disparado: {$this->result->itemName} em {$this->alert->server->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alert-triggered',
        );
    }
}
