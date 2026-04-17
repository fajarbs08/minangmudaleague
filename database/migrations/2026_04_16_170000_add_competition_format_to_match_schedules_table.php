<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_schedules', function (Blueprint $table) {
            $table->string('competition_format', 20)
                ->default('league')
                ->after('age_group_id');
        });

        DB::table('match_schedules')
            ->whereNull('competition_format')
            ->update(['competition_format' => 'league']);
    }

    public function down(): void
    {
        Schema::table('match_schedules', function (Blueprint $table) {
            $table->dropColumn('competition_format');
        });
    }
};
