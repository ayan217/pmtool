<?php

namespace App\Mail;

use App\Enums\DeadlineType;
use App\Mail\Concerns\AttachesTaskDocuments;
use App\Mail\Concerns\UsesConfiguredFromName;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class DeadlineReminderMail extends Mailable implements ShouldQueue
{
    use AttachesTaskDocuments, Queueable, SerializesModels, UsesConfiguredFromName;

    public function __construct(
        public Task $task,
        public DeadlineType $deadlineType,
        public Carbon $deadline,
        ?string $fromName = null,
    ) {
        $this->fromName = $fromName;
    }

    public function envelope(): Envelope
    {
        return $this->envelopeWithSubject('Deadline approaching: '.$this->task->title);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.deadline-reminder',
        );
    }
}
