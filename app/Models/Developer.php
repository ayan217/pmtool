<?php

namespace App\Models;

use Database\Factories\DeveloperFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Developer extends Model
{
    /** @use HasFactory<DeveloperFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
    ];

    /**
     * @return Collection<int, array{name: string, email: ?string, phone: ?string}>
     */
    public static function catalog(): Collection
    {
        return static::query()
            ->orderBy('name')
            ->get(['name', 'email', 'phone'])
            ->map(fn (self $developer): array => [
                'name' => $developer->name,
                'email' => $developer->email,
                'phone' => $developer->phone,
            ])
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    public static function suggestionNames(): Collection
    {
        $names = static::query()->orderBy('name')->pluck('name');

        if ($names->isNotEmpty() || ! Schema::hasTable('task_developers')) {
            return $names;
        }

        return TaskDeveloper::query()
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');
    }

    public static function findByName(string $name): ?self
    {
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        return static::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();
    }

    public static function upsertFromAssignment(string $name, ?string $email = null, ?string $phone = null): self
    {
        $name = trim($name);
        $email = filled($email) ? trim((string) $email) : null;
        $phone = filled($phone) ? trim((string) $phone) : null;

        $developer = static::findByName($name);

        if ($developer === null) {
            return static::query()->create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
            ]);
        }

        $updates = [];

        if (! filled($developer->email) && $email !== null) {
            $updates['email'] = $email;
        }

        if (! filled($developer->phone) && $phone !== null) {
            $updates['phone'] = $phone;
        }

        if ($updates !== []) {
            $developer->update($updates);
        }

        return $developer;
    }

    public static function importFromExistingAssignments(): void
    {
        if (Schema::hasTable('task_developers')) {
            $assignments = DB::table('task_developers')
                ->whereNotNull('name')
                ->where('name', '!=', '')
                ->orderBy('id')
                ->get(['name', 'email', 'phone']);

            foreach ($assignments as $assignment) {
                foreach (explode(',', (string) $assignment->name) as $name) {
                    $name = trim($name);

                    if ($name === '') {
                        continue;
                    }

                    static::upsertFromAssignment($name, $assignment->email, $assignment->phone);
                }
            }
        }

        if (! Schema::hasTable('tasks')) {
            return;
        }

        $legacyNames = DB::table('tasks')
            ->whereNotNull('developer')
            ->where('developer', '!=', '')
            ->pluck('developer');

        foreach ($legacyNames as $legacy) {
            foreach (explode(',', (string) $legacy) as $name) {
                $name = trim($name);

                if ($name !== '') {
                    static::upsertFromAssignment($name);
                }
            }
        }
    }
}
