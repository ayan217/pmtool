<?php

namespace App\Enums;

enum DeadlineType: string
{
    case Dev = 'dev';
    case Client = 'client';

    public function label(): string
    {
        return match ($this) {
            self::Dev => 'Development Deadline',
            self::Client => 'Client Deadline',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Dev => 'DEV',
            self::Client => 'CLIENT',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Dev => 'badge-dev',
            self::Client => 'badge-client',
        };
    }

    public function settingEnabledKey(): string
    {
        return match ($this) {
            self::Dev => 'notify_dev_deadlines',
            self::Client => 'notify_client_deadlines',
        };
    }

    public function settingHoursKey(): string
    {
        return match ($this) {
            self::Dev => 'reminder_hours_dev',
            self::Client => 'reminder_hours_client',
        };
    }
}
