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
            $table->string('round_label')->nullable()->after('competition_format');
            $table->unsignedSmallInteger('round_order')->nullable()->after('round_label');
            $table->unsignedSmallInteger('bracket_slot')->nullable()->after('round_order');
        });

        DB::table('match_schedules')
            ->where('competition_format', 'knockout')
            ->update([
                'round_label' => 'Babak Knockout',
                'round_order' => 1,
                'bracket_slot' => 1,
            ]);
    }

    public function down(): void
    {
        Schema::table('match_schedules', function (Blueprint $table) {
            $table->dropColumn(['round_label', 'round_order', 'bracket_slot']);
        });
    }
};
