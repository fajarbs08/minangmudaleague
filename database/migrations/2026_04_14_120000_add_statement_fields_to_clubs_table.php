<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->string('manager_title', 255)->nullable()->after('manager_name');
            $table->string('statement_age_group', 50)->nullable()->after('training_address');
            $table->string('statement_contact', 100)->nullable()->after('statement_age_group');
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn(['manager_title', 'statement_age_group', 'statement_contact']);
        });
    }
};
