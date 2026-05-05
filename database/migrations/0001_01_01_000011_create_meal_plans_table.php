<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diet_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->enum('meal_type', ['breakfast', 'snack_am', 'lunch', 'snack_pm', 'dinner']);
            $table->string('meal_name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('calories')->default(0);
            $table->unsignedSmallInteger('fat_g')->default(0);
            $table->unsignedSmallInteger('protein_g')->default(0);
            $table->unsignedSmallInteger('carb_g')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_plans');
    }
};
