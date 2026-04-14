<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['clubs', 'officials', 'players', 'lineup_lists'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('verification_status', 20)->default('draft')->after('notes');
                $table->text('verification_notes')->nullable()->after('verification_status');
                $table->timestamp('submitted_at')->nullable()->after('verification_notes');
                $table->foreignId('reviewed_by')->nullable()->after('submitted_at')->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            });
        }
    }

    public function down(): void
    {
        foreach (['clubs', 'officials', 'players', 'lineup_lists'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('reviewed_by');
                $table->dropColumn([
                    'verification_status',
                    'verification_notes',
                    'submitted_at',
                    'reviewed_at',
                ]);
            });
        }
    }
};
