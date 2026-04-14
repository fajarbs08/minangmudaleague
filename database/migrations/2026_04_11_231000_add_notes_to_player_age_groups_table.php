<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_age_groups', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('status_date');
        });
    }

    public function down(): void
    {
        Schema::table('player_age_groups', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
