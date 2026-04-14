<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('age_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('club_a_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('club_b_id')->constrained('clubs')->cascadeOnDelete();
            $table->string('match_day');
            $table->string('venue');
            $table->date('match_date');
            $table->time('kickoff_time');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_schedules');
    }
};
