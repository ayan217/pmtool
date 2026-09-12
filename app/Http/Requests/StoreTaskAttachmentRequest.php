<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesTaskAttachments;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskAttachmentRequest extends FormRequest
{
    use ValidatesTaskAttachments;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->attachmentRules(), [
            'attachments' => ['required', 'array', 'min:1', 'max:'.(int) config('pm.attachments.max_files', 20)],
        ]);
    }
}
