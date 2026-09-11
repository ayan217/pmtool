<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deadline_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('deadline_type');
            $table->dateTime('deadline_at');
            $table->dateTime('scheduled_for');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['task_id', 'deadline_type', 'deadline_at'], 'deadline_notifications_unique');
            $table->index(['deadline_type', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deadline_notifications');
    }
};
