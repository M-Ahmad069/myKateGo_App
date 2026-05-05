<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('plan_status', 32)->nullable()->after('coaching_tags');
        });

        Schema::table('diet_plans', function (Blueprint $table) {
            $table->text('plan_summary')->nullable()->after('plan_type');
            $table->json('key_foods')->nullable()->after('plan_summary');
            $table->json('foods_to_avoid')->nullable()->after('key_foods');
            $table->text('daily_tip')->nullable()->after('foods_to_avoid');
        });

        Schema::table('meal_plans', function (Blueprint $table) {
            $table->string('day_name', 16)->nullable()->after('day_of_week');
            $table->unsignedSmallInteger('prep_time_min')->nullable()->after('description');
            $table->json('ingredients')->nullable()->after('carb_g');
        });

        Schema::table('workout_plans', function (Blueprint $table) {
            $table->string('day_name', 16)->nullable()->after('day_of_week');
            $table->json('warm_up')->nullable()->after('exercises');
            $table->json('cool_down')->nullable()->after('warm_up');
            $table->string('intensity', 32)->nullable()->after('duration_minutes');
            $table->unsignedInteger('calories_burned_estimate')->nullable()->after('intensity');
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant']);
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');

        Schema::table('workout_plans', function (Blueprint $table) {
            $table->dropColumn(['day_name', 'warm_up', 'cool_down', 'intensity', 'calories_burned_estimate']);
        });

        Schema::table('meal_plans', function (Blueprint $table) {
            $table->dropColumn(['day_name', 'prep_time_min', 'ingredients']);
        });

        Schema::table('diet_plans', function (Blueprint $table) {
            $table->dropColumn(['plan_summary', 'key_foods', 'foods_to_avoid', 'daily_tip']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('plan_status');
        });
    }
};
