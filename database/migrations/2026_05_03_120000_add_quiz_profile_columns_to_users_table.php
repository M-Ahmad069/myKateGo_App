<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('quiz_profile')->nullable()->after('goal');
            $table->string('plan_segment', 191)->nullable()->after('quiz_profile');
            $table->json('coaching_tags')->nullable()->after('plan_segment');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['quiz_profile', 'plan_segment', 'coaching_tags']);
        });
    }
};
