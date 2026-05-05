<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('logged_date');
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->decimal('water_liters', 4, 2)->nullable();
            $table->unsignedInteger('steps')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'logged_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_logs');
    }
};
