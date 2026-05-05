<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diet_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('daily_calories');
            $table->unsignedTinyInteger('fat_pct');
            $table->unsignedTinyInteger('protein_pct');
            $table->unsignedTinyInteger('carb_pct');
            $table->unsignedInteger('fat_grams');
            $table->unsignedInteger('protein_grams');
            $table->unsignedInteger('carb_grams');
            $table->string('plan_type');
            $table->unsignedSmallInteger('estimated_weeks_to_goal');
            $table->longText('raw_plan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diet_plans');
    }
};
