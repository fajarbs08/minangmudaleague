<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lineup_lists', function (Blueprint $table) {
            $table->foreignId('match_id')->nullable()->after('age_group_id')->constrained('match_schedules')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lineup_lists', function (Blueprint $table) {
            $table->dropConstrainedForeignId('match_id');
        });
    }
};
