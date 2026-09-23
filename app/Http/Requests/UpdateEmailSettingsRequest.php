<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $time = trim((string) $this->input('daily_reminder_time', ''));

        if (preg_match('/^(\d{2}:\d{2}):\d{2}$/', $time, $matches) === 1) {
            $this->merge(['daily_reminder_time' => $matches[1]]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'from_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'daily_reminder_time' => ['required', 'date_format:H:i'],
        ];
    }
}
