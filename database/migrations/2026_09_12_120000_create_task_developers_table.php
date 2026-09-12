<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_developers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('task_id');
            $table->index('name');
            $table->index('email');
            $table->index('phone');
        });

        $tasks = DB::table('tasks')
            ->whereNotNull('developer')
            ->where('developer', '!=', '')
            ->get(['id', 'developer']);

        foreach ($tasks as $task) {
            DB::table('task_developers')->insert([
                'task_id' => $task->id,
                'name' => $task->developer,
                'email' => null,
                'phone' => null,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('task_developers');
    }
};
