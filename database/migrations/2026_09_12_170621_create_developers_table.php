<?php

use App\Models\Developer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('developers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();

            $table->unique('name');
            $table->index('email');
            $table->index('phone');
        });

        Developer::importFromExistingAssignments();
    }

    public function down(): void
    {
        Schema::dropIfExists('developers');
    }
};
