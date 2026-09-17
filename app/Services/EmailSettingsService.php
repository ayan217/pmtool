<?php

namespace App\Services;

use App\Models\User;

class EmailSettingsService
{
    public const KEY_FROM_NAME = 'mail_from_name';

    public const KEY_ADMIN_EMAIL = 'admin_email';

    public function __construct(protected SettingsService $settings) {}

    /**
     * @return array{from_name: string, admin_email: string}
     */
    public function forUser(User $user): array
    {
        return [
            'from_name' => $this->fromName($user),
            'admin_email' => $this->adminEmail($user),
        ];
    }

    public function fromName(User $user): string
    {
        $stored = $this->storedValue($user, self::KEY_FROM_NAME);

        if ($stored !== null) {
            return $stored;
        }

        $name = trim((string) $user->name);

        return $name !== '' ? $name : (string) config('mail.from.name');
    }

    public function adminEmail(User $user): string
    {
        return $this->storedValue($user, self::KEY_ADMIN_EMAIL) ?? $user->email;
    }

    public function update(User $user, string $fromName, string $adminEmail): void
    {
        $this->settings->set($user, self::KEY_FROM_NAME, $fromName);
        $this->settings->set($user, self::KEY_ADMIN_EMAIL, $adminEmail);
        $user->unsetRelation('settings');
    }

    protected function storedValue(User $user, string $key): ?string
    {
        if (! $user->relationLoaded('settings')) {
            $user->load('settings');
        }

        $value = trim((string) ($user->settings->firstWhere('key', $key)?->value ?? ''));

        return $value !== '' ? $value : null;
    }
}
