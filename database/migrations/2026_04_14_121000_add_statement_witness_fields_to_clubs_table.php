<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->string('statement_witness_name', 255)->nullable()->after('statement_contact');
            $table->string('statement_witness_title', 255)->nullable()->after('statement_witness_name');
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn(['statement_witness_name', 'statement_witness_title']);
        });
    }
};
