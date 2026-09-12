<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Carbon;

trait CombinesDeadlineFields
{
    protected function prepareForValidation(): void
    {
        $developers = $this->normalizeDevelopers();

        $this->merge([
            'project_id' => $this->normalizeProjectId($this->input('project_id')),
            'dev_deadline' => $this->combineDateTime('dev_deadline_date', 'dev_deadline_time'),
            'client_deadline' => $this->combineDateTime('client_deadline_date', 'client_deadline_time'),
            'developers' => $developers,
            'developer' => collect($developers)->pluck('name')->filter()->implode(', ') ?: $this->blankToNull($this->input('developer')),
            'description' => $this->blankToNull($this->input('description')),
            'notes' => $this->blankToNull($this->input('notes')),
        ]);
    }

    /**
     * @return list<array{name: string, email: ?string, phone: ?string}>
     */
    protected function normalizeDevelopers(): array
    {
        $rows = $this->input('developers');

        if (! is_array($rows) || $rows === []) {
            $legacy = $this->blankToNull($this->input('developer'));

            return $legacy ? [['name' => $legacy, 'email' => null, 'phone' => null]] : [];
        }

        return collect($rows)
            ->map(function (mixed $row): ?array {
                if (! is_array($row)) {
                    return null;
                }

                $name = $this->blankToNull($row['name'] ?? null);
                $email = $this->blankToNull($row['email'] ?? null);
                $phone = $this->blankToNull($row['phone'] ?? null);

                if ($name === null && $email === null && $phone === null) {
                    return null;
                }

                return [
                    'name' => $name ?? 'Developer',
                    'email' => $email,
                    'phone' => $phone,
                ];
            })
            ->filter()
            ->values()
            ->all();
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
