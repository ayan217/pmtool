<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('deadline_type')->nullable();
            $table->json('recipients');
            $table->string('subject');
            $table->text('body')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['type', 'sent_at']);
            $table->index(['task_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
