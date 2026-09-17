<?php

namespace App\Mail\Concerns;

use App\Models\TaskAttachment;
use Illuminate\Mail\Mailables\Attachment;

trait AttachesTaskDocuments
{
    /**
     * @return list<Attachment>
     */
    public function attachments(): array
    {
        $this->task->loadMissing('attachments');

        return $this->task->attachments
            ->filter(fn (TaskAttachment $attachment) => $attachment->existsOnDisk())
            ->map(function (TaskAttachment $attachment): Attachment {
                $file = Attachment::fromStorageDisk($attachment->disk, $attachment->path)
                    ->as($attachment->original_name);

                $mime = trim((string) $attachment->mime_type);

                return $mime !== '' ? $file->withMime($mime) : $file;
            })
            ->values()
            ->all();
    }
}
