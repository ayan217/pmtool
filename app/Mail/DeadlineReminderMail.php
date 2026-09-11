<?php

namespace App\Mail;

use App\Enums\DeadlineType;
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
    use Queueable, SerializesModels;

    public function __construct(
        public Task $task,
        public DeadlineType $deadlineType,
        public Carbon $deadline,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Deadline approaching: '.$this->task->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.deadline-reminder',
        );
    }
}
