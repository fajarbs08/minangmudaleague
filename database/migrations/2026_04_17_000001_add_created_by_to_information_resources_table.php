<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('information_resources', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('is_published')
                ->constrained('users')
                ->nullOnDelete();
        });

        $adminId = DB::table('users')
            ->where('role', 'admin')
            ->orderBy('id')
            ->value('id');

        if ($adminId) {
            DB::table('information_resources')
                ->whereNull('created_by')
                ->update(['created_by' => $adminId]);
        }
    }

    public function down(): void
    {
        Schema::table('information_resources', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
