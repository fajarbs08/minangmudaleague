<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_schedules', function (Blueprint $table) {
            $table->foreignId('source_match_a_id')
                ->nullable()
                ->after('bracket_slot')
                ->constrained('match_schedules')
                ->nullOnDelete();
            $table->foreignId('source_match_b_id')
                ->nullable()
                ->after('source_match_a_id')
                ->constrained('match_schedules')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('match_schedules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_match_a_id');
            $table->dropConstrainedForeignId('source_match_b_id');
        });
    }
};
