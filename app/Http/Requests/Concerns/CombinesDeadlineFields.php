<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Carbon;

trait CombinesDeadlineFields
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'project_id' => $this->normalizeProjectId($this->input('project_id')),
            'dev_deadline' => $this->combineDateTime('dev_deadline_date', 'dev_deadline_time'),
            'client_deadline' => $this->combineDateTime('client_deadline_date', 'client_deadline_time'),
            'developer' => $this->blankToNull($this->input('developer')),
            'description' => $this->blankToNull($this->input('description')),
            'notes' => $this->blankToNull($this->input('notes')),
        ]);
    }

    protected function combineDateTime(string $dateField, string $timeField): ?string
    {
        $date = $this->blankToNull($this->input($dateField));

        if ($date === null) {
            return null;
        }

        $time = $this->blankToNull($this->input($timeField)) ?? '00:00';

        try {
            return Carbon::parse($date.' '.$time)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return $date.' '.$time;
        }
    }

    protected function normalizeProjectId(mixed $value): mixed
    {
        if ($value === '' || $value === '0' || $value === 'none' || $value === null) {
            return null;
        }

        return $value;
    }

    protected function blankToNull(mixed $value): mixed
    {
        if (! is_scalar($value)) {
            return $value;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
