<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Blocked = 'blocked';
    case Completed = 'completed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProgress => 'In Progress',
            self::Blocked => 'Blocked',
            self::Completed => 'Completed',
            self::Archived => 'Archived',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-secondary',
            self::InProgress => 'bg-primary',
            self::Blocked => 'bg-warning text-dark',
            self::Completed => 'bg-success',
            self::Archived => 'bg-dark',
        };
    }

    /**
     * @return list<self>
     */
    public static function activeCases(): array
    {
        return [self::Pending, self::InProgress, self::Blocked];
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
