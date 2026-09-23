<?php

namespace App\Services;

use App\Models\User;

class EmailSettingsService
{
    public const KEY_FROM_NAME = 'mail_from_name';

    public const KEY_ADMIN_EMAIL = 'admin_email';

    public const KEY_DAILY_REMINDER_TIME = 'daily_reminder_time';

    public function __construct(protected SettingsService $settings) {}

    /**
     * @return array{from_name: string, admin_email: string, daily_reminder_time: string}
     */
    public function forUser(User $user): array
    {
        return [
            'from_name' => $this->fromName($user),
            'admin_email' => $this->adminEmail($user),
            'daily_reminder_time' => $this->dailyReminderTime($user),
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

    public function dailyReminderTime(User $user): string
    {
        $stored = $this->normalizeTime($this->storedValue($user, self::KEY_DAILY_REMINDER_TIME));

        return $stored ?? (string) config('pm.daily_reminder_time', '18:00');
    }

    public function update(User $user, string $fromName, string $adminEmail, ?string $dailyReminderTime = null): void
    {
        $this->settings->set($user, self::KEY_FROM_NAME, $fromName);
        $this->settings->set($user, self::KEY_ADMIN_EMAIL, $adminEmail);

        $time = $this->normalizeTime($dailyReminderTime);

        if ($time !== null) {
            $this->settings->set($user, self::KEY_DAILY_REMINDER_TIME, $time);
        }

        $user->unsetRelation('settings');
    }

    protected function normalizeTime(mixed $value): ?string
    {
        $value = trim((string) $value);

        if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)(?::[0-5]\d)?$/', $value, $matches) !== 1) {
            return null;
        }

        return $matches[1].':'.$matches[2];
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
