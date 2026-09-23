<?php

namespace App\Enums;

enum EmailLogType: string
{
    case DeadlineReminder = 'deadline_reminder';
    case StatusReminder = 'status_reminder';
    case DailyReminder = 'daily_reminder';

    public function label(): string
    {
        return match ($this) {
            self::DeadlineReminder => 'Deadline reminder',
            self::StatusReminder => 'Status reminder',
            self::DailyReminder => 'Daily reminder',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DeadlineReminder => 'badge-dev',
            self::StatusReminder => 'bg-dark',
            self::DailyReminder => 'bg-primary',
        };
    }
}
