<?php

namespace App\Mail\Concerns;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;

trait UsesConfiguredFromName
{
    public ?string $fromName = null;

    protected function envelopeWithSubject(string $subject): Envelope
    {
        $name = trim((string) $this->fromName);

        return new Envelope(
            from: new Address(
                (string) config('mail.from.address'),
                $name !== '' ? $name : (string) config('mail.from.name'),
            ),
            subject: $subject,
        );
    }
}
