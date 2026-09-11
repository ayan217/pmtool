<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('developer')->nullable();
            $table->string('status')->default('pending');
            $table->string('priority')->default('medium');
            $table->string('previous_status')->nullable();
            $table->dateTime('dev_deadline')->nullable();
            $table->dateTime('client_deadline')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index('project_id');
            $table->index('status');
            $table->index('priority');
            $table->index('developer');
            $table->index('dev_deadline');
            $table->index('client_deadline');
            $table->index('completed_at');
            $table->index('archived_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
