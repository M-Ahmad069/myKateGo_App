<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('week_number');
            $table->unsignedTinyInteger('day_of_week');
            $table->enum('workout_type', ['strength', 'cardio', 'hiit', 'rest', 'flexibility']);
            $table->string('workout_name');
            $table->unsignedSmallInteger('duration_minutes')->default(0);
            $table->json('exercises')->nullable();
            $table->enum('location', ['home', 'gym', 'either']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_plans');
    }
};
