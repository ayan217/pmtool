<?php

namespace App\Mail;

use App\Mail\Concerns\AttachesTaskDocuments;
use App\Mail\Concerns\UsesConfiguredFromName;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StatusReminderMail extends Mailable implements ShouldQueue
{
    use AttachesTaskDocuments, Queueable, SerializesModels, UsesConfiguredFromName;

    public function __construct(
        public Task $task,
        public string $subjectLine,
        public string $bodyText,
        ?string $fromName = null,
    ) {
        $this->fromName = $fromName;
    }

    public function envelope(): Envelope
    {
        return $this->envelopeWithSubject($this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.status-reminder',
        );
    }
}
