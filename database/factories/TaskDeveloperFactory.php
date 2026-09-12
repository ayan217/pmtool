<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskDeveloper;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskDeveloper>
 */
class TaskDeveloperFactory extends Factory
{
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'name' => fake()->firstName(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->numerify('+91##########'),
            'sort_order' => 0,
        ];
    }
}
