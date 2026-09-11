<?php

namespace App\Services;

use App\Enums\DeadlineType;
use App\Models\Setting;
use App\Models\User;

class SettingsService
{
    public const KEY_NOTIFY_DEV = 'notify_dev_deadlines';

    public const KEY_NOTIFY_CLIENT = 'notify_client_deadlines';

    public const KEY_HOURS_DEV = 'reminder_hours_dev';

    public const KEY_HOURS_CLIENT = 'reminder_hours_client';

    /**
     * @return array<string, mixed>
     */
    public function all(User $user): array
    {
        $stored = $user->settings()->pluck('value', 'key');

        return [
            self::KEY_NOTIFY_DEV => $this->toBoolean($stored->get(self::KEY_NOTIFY_DEV), true),
            self::KEY_NOTIFY_CLIENT => $this->toBoolean($stored->get(self::KEY_NOTIFY_CLIENT), true),
            self::KEY_HOURS_DEV => $this->toHours($stored->get(self::KEY_HOURS_DEV)),
            self::KEY_HOURS_CLIENT => $this->toHours($stored->get(self::KEY_HOURS_CLIENT)),
        ];
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function update(User $user, array $values): void
    {
        foreach ($values as $key => $value) {
            $this->set($user, $key, is_bool($value) ? ($value ? '1' : '0') : (string) $value);
        }
    }

    public function set(User $user, string $key, string $value): void
    {
        Setting::query()->updateOrCreate(
            ['user_id' => $user->id, 'key' => $key],
            ['value' => $value],
        );
    }

    public function notificationsEnabled(User $user, DeadlineType $type): bool
    {
        return (bool) $this->all($user)[$type->settingEnabledKey()];
    }

    public function reminderHours(User $user, DeadlineType $type): int
    {
        return (int) $this->all($user)[$type->settingHoursKey()];
    }

    protected function toBoolean(mixed $value, bool $default): bool
    {
        if ($value === null) {
            return $default;
        }

        return in_array((string) $value, ['1', 'true', 'on', 'yes'], true);
    }

    protected function toHours(mixed $value): int
    {
        $hours = (int) ($value ?? config('pm.default_reminder_hours'));
        $allowed = config('pm.reminder_hours');

        return in_array($hours, $allowed, true) ? $hours : (int) config('pm.default_reminder_hours');
    }
}
