<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDeveloper extends Model
{
    /** @use HasFactory<\Database\Factories\TaskDeveloperFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'task_id',
        'name',
        'email',
        'phone',
        'sort_order',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
