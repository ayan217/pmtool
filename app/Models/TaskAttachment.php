<?php

namespace App\Models;

use Database\Factories\TaskAttachmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskAttachment extends Model
{
    /** @use HasFactory<TaskAttachmentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'task_id',
        'original_name',
        'disk',
        'path',
        'mime_type',
        'size',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (TaskAttachment $attachment): void {
            $attachment->deleteFile();
        });
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function humanSize(): string
    {
        return Number::fileSize($this->size);
    }

    public function extension(): string
    {
        return strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
    }

    public function icon(): string
    {
        return match ($this->extension()) {
            'pdf' => 'bi-file-earmark-pdf',
            'doc', 'docx' => 'bi-file-earmark-word',
            'xls', 'xlsx', 'csv' => 'bi-file-earmark-excel',
            'ppt', 'pptx' => 'bi-file-earmark-ppt',
            'zip', 'rar', '7z' => 'bi-file-earmark-zip',
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'avif' => 'bi-file-earmark-image',
            'mp4', 'webm', 'mov', 'ogg', 'ogv', 'm4v', 'avi' => 'bi-film',
            default => 'bi-file-earmark-text',
        };
    }

    public function isPdf(): bool
    {
        return $this->extension() === 'pdf'
            || str_contains(strtolower((string) $this->mime_type), 'pdf');
    }

    public function isImage(): bool
    {
        if (str_starts_with(strtolower((string) $this->mime_type), 'image/')) {
            return true;
        }

        return in_array($this->extension(), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'avif'], true);
    }

    public function isVideo(): bool
    {
        if (str_starts_with(strtolower((string) $this->mime_type), 'video/')) {
            return true;
        }

        return in_array($this->extension(), ['mp4', 'webm', 'mov', 'ogg', 'ogv', 'm4v', 'avi'], true);
    }

    public function isPreviewable(): bool
    {
        return $this->isImage() || $this->isVideo() || $this->isPdf();
    }

    public function existsOnDisk(): bool
    {
        return $this->path !== '' && Storage::disk($this->disk)->exists($this->path);
    }

    public function download(): StreamedResponse
    {
        abort_unless($this->existsOnDisk(), 404);

        return Storage::disk($this->disk)->download($this->path, $this->original_name);
    }

    public function inlineResponse(): StreamedResponse
    {
        abort_unless($this->existsOnDisk(), 404);

        $headers = [];
        $mime = trim((string) $this->mime_type);

        if ($mime !== '') {
            $headers['Content-Type'] = $mime;
        }

        return Storage::disk($this->disk)->response($this->path, $this->original_name, $headers);
    }

    public function deleteFile(): void
    {
        if ($this->path === '') {
            return;
        }

        Storage::disk($this->disk)->delete($this->path);
    }
}
