<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_age_groups', function (Blueprint $table) {
            $table->boolean('is_starter')->default(false)->after('notes');
            $table->boolean('is_substitute')->default(false)->after('is_starter');
        });
    }

    public function down(): void
    {
        Schema::table('player_age_groups', function (Blueprint $table) {
            $table->dropColumn(['is_starter', 'is_substitute']);
        });
    }
};
