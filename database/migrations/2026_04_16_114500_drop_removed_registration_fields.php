<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $columns = [
                'registration_number',
                'nisn',
                'non_nisn',
                'passport_number',
                'nisn_file_path',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('players', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('officials', function (Blueprint $table) {
            if (Schema::hasColumn('officials', 'passport_number')) {
                $table->dropColumn('passport_number');
            }
        });

        Schema::table('clubs', function (Blueprint $table) {
            $columns = [
                'city',
                'mailing_address',
                'deed_file_path',
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
            if (!Schema::hasColumn('clubs', 'city')) {
                $table->string('city')->nullable()->after('zone');
            }

            if (!Schema::hasColumn('clubs', 'mailing_address')) {
                $table->text('mailing_address')->nullable()->after('address');
            }

            if (!Schema::hasColumn('clubs', 'deed_file_path')) {
                $table->string('deed_file_path')->nullable()->after('logo_url');
            }
        });

        Schema::table('officials', function (Blueprint $table) {
            if (!Schema::hasColumn('officials', 'passport_number')) {
                $table->string('passport_number')->nullable()->after('identity_number');
            }
        });

        Schema::table('players', function (Blueprint $table) {
            if (!Schema::hasColumn('players', 'registration_number')) {
                $table->string('registration_number')->nullable()->after('name');
            }

            if (!Schema::hasColumn('players', 'nisn')) {
                $table->string('nisn')->nullable()->after('citizenship');
            }

            if (!Schema::hasColumn('players', 'non_nisn')) {
                $table->string('non_nisn')->nullable()->after('nisn');
            }

            if (!Schema::hasColumn('players', 'passport_number')) {
                $table->string('passport_number')->nullable()->after('non_nisn');
            }

            if (!Schema::hasColumn('players', 'nisn_file_path')) {
                $table->string('nisn_file_path')->nullable()->after('photo_path');
            }
        });
    }
};
