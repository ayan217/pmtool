<?php

namespace App\Http\Requests\Concerns;

use App\Models\Developer;
use Closure;

trait ValidatesDeveloperProfile
{
    /**
     * @return array<string, mixed>
     */
    protected function profileRules(?int $ignoreId = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:120',
                function (string $attribute, mixed $value, Closure $fail) use ($ignoreId): void {
                    $exists = Developer::query()
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim((string) $value))])
                        ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                        ->exists();

                    if ($exists) {
                        $fail('A developer with this name already exists.');
                    }
                },
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->blankToNull($this->input('name')),
            'email' => $this->blankToNull($this->input('email')),
            'phone' => $this->blankToNull($this->input('phone')),
        ]);
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
