<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $columns = [
                'statement_age_group',
                'statement_contact',
                'statement_witness_name',
                'statement_witness_title',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('clubs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            if (!Schema::hasColumn('clubs', 'statement_age_group')) {
                $table->string('statement_age_group', 50)->nullable()->after('training_address');
            }

            if (!Schema::hasColumn('clubs', 'statement_contact')) {
                $table->string('statement_contact', 100)->nullable()->after('statement_age_group');
            }

            if (!Schema::hasColumn('clubs', 'statement_witness_name')) {
                $table->string('statement_witness_name')->nullable()->after('statement_contact');
            }

            if (!Schema::hasColumn('clubs', 'statement_witness_title')) {
                $table->string('statement_witness_title')->nullable()->after('statement_witness_name');
            }
        });
    }
};
