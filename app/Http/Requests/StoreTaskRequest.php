<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\Concerns\CombinesDeadlineFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    use CombinesDeadlineFields;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')],
            'developer' => ['nullable', 'string', 'max:120'],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'status' => ['required', Rule::in([
                TaskStatus::Pending->value,
                TaskStatus::InProgress->value,
                TaskStatus::Blocked->value,
            ])],
            'description' => ['nullable', 'string', 'max:10000'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'dev_deadline_date' => ['nullable', 'date'],
            'dev_deadline_time' => ['nullable', 'date_format:H:i'],
            'client_deadline_date' => ['nullable', 'date'],
            'client_deadline_time' => ['nullable', 'date_format:H:i'],
            'dev_deadline' => ['nullable', 'date'],
            'client_deadline' => ['nullable', 'date'],
        ];
    }
}
