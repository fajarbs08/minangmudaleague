<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_schedules', function (Blueprint $table) {
            $table->unsignedTinyInteger('score_club_a')->nullable()->after('kickoff_time');
            $table->unsignedTinyInteger('score_club_b')->nullable()->after('score_club_a');
            $table->boolean('is_finished')->default(false)->after('score_club_b');
        });
    }

    public function down(): void
    {
        Schema::table('match_schedules', function (Blueprint $table) {
            $table->dropColumn(['score_club_a', 'score_club_b', 'is_finished']);
        });
    }
};
