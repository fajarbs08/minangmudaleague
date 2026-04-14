<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('officials', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('license_number');
            $table->string('license_file_path')->nullable()->after('photo_path');
            $table->string('identity_file_path')->nullable()->after('license_file_path');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('position');
            $table->string('nisn_file_path')->nullable()->after('photo_path');
            $table->string('diploma_file_path')->nullable()->after('nisn_file_path');
            $table->string('report_file_path')->nullable()->after('diploma_file_path');
            $table->string('birth_certificate_file_path')->nullable()->after('report_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('officials', function (Blueprint $table) {
            $table->dropColumn([
                'photo_path',
                'license_file_path',
                'identity_file_path',
            ]);
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn([
                'photo_path',
                'nisn_file_path',
                'diploma_file_path',
                'report_file_path',
                'birth_certificate_file_path',
            ]);
        });
    }
};
