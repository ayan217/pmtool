<?php

namespace App\Http\Requests\Concerns;

use Closure;
use Illuminate\Http\UploadedFile;

trait ValidatesTaskAttachments
{
    /**
     * @return array<string, mixed>
     */
    protected function attachmentRules(): array
    {
        $maxFiles = (int) config('pm.attachments.max_files', 20);
        $maxKilobytes = (int) config('pm.attachments.max_kilobytes', 25600);
        $blocked = config('pm.attachments.blocked_extensions', []);

        return [
            'attachments' => ['nullable', 'array', 'max:'.$maxFiles],
            'attachments.*' => [
                'file',
                'max:'.$maxKilobytes,
                function (string $attribute, mixed $value, Closure $fail) use ($blocked): void {
                    if (! $value instanceof UploadedFile) {
                        return;
                    }

                    $extension = strtolower($value->getClientOriginalExtension());

                    if (in_array($extension, $blocked, true)) {
                        $fail('This file type cannot be attached.');
                    }
                },
            ],
        ];
    }
}
