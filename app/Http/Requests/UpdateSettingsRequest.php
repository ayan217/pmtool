<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'notify_dev_deadlines' => $this->boolean('notify_dev_deadlines'),
            'notify_client_deadlines' => $this->boolean('notify_client_deadlines'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $hours = config('pm.reminder_hours');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'notify_dev_deadlines' => ['required', 'boolean'],
            'notify_client_deadlines' => ['required', 'boolean'],
            'reminder_hours_dev' => ['required', 'integer', Rule::in($hours)],
            'reminder_hours_client' => ['required', 'integer', Rule::in($hours)],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ];
    }
}
