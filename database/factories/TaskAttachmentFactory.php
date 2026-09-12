<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TaskAttachment>
 */
class TaskAttachmentFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->word().'.pdf';

        return [
            'task_id' => Task::factory(),
            'original_name' => $name,
            'disk' => 'local',
            'path' => 'tasks/'.Str::uuid().'/'.$name,
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'sort_order' => 0,
        ];
    }
}
