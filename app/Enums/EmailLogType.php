<?php

namespace App\Enums;

enum EmailLogType: string
{
    case DeadlineReminder = 'deadline_reminder';
    case StatusReminder = 'status_reminder';

    public function label(): string
    {
        return match ($this) {
            self::DeadlineReminder => 'Deadline reminder',
            self::StatusReminder => 'Status reminder',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DeadlineReminder => 'badge-dev',
            self::StatusReminder => 'bg-dark',
        };
    }
}
