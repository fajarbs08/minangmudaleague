<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->text('mailing_address')->nullable()->after('address');
            $table->text('training_address')->nullable()->after('mailing_address');
            $table->string('deed_file_path')->nullable()->after('logo_url');
            $table->string('statement_file_path')->nullable()->after('deed_file_path');
        });

        Schema::table('officials', function (Blueprint $table) {
            $table->foreignId('age_group_id')->nullable()->after('club_id')->constrained()->nullOnDelete();
            $table->string('birth_place')->nullable()->after('email');
            $table->string('citizenship', 20)->nullable()->after('birth_place');
            $table->string('identity_number')->nullable()->after('citizenship');
            $table->string('passport_number')->nullable()->after('identity_number');
            $table->string('license_levels')->nullable()->after('license_number');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->string('mother_name')->nullable()->after('name');
            $table->string('school_name')->nullable()->after('mother_name');
            $table->string('citizenship', 20)->nullable()->after('position');
            $table->string('nisn')->nullable()->after('citizenship');
            $table->string('non_nisn')->nullable()->after('nisn');
            $table->string('passport_number')->nullable()->after('non_nisn');
            $table->string('birth_place')->nullable()->after('passport_number');
            $table->string('dominant_foot')->nullable()->after('weight_kg');
        });

        Schema::table('lineup_lists', function (Blueprint $table) {
            $table->string('jersey_color')->nullable()->after('coach_name');
            $table->string('goalkeeper_jersey_color')->nullable()->after('jersey_color');
            $table->string('played_at')->nullable()->after('goalkeeper_jersey_color');
            $table->time('played_time')->nullable()->after('match_date');
        });

        DB::table('clubs')
            ->whereNull('mailing_address')
            ->update([
                'mailing_address' => DB::raw('address'),
                'training_address' => DB::raw('address'),
            ]);

        DB::table('players')
            ->whereNull('citizenship')
            ->update(['citizenship' => 'WNI']);

        DB::table('officials')
            ->whereNull('citizenship')
            ->update(['citizenship' => 'WNI']);

        DB::table('lineup_lists')
            ->whereNull('played_at')
            ->update(['played_at' => DB::raw('match_day')]);

        $clubAgeGroups = DB::table('players')
            ->select('club_id', DB::raw('MIN(primary_age_group_id) as age_group_id'))
            ->whereNotNull('primary_age_group_id')
            ->groupBy('club_id')
            ->pluck('age_group_id', 'club_id');

        foreach ($clubAgeGroups as $clubId => $ageGroupId) {
            DB::table('officials')
                ->where('club_id', $clubId)
                ->whereNull('age_group_id')
                ->update(['age_group_id' => $ageGroupId]);
        }
    }

    public function down(): void
    {
        Schema::table('lineup_lists', function (Blueprint $table) {
            $table->dropColumn([
                'jersey_color',
                'goalkeeper_jersey_color',
                'played_at',
                'played_time',
            ]);
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn([
                'mother_name',
                'school_name',
                'citizenship',
                'nisn',
                'non_nisn',
                'passport_number',
                'birth_place',
                'dominant_foot',
            ]);
        });

        Schema::table('officials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('age_group_id');
            $table->dropColumn([
                'birth_place',
                'citizenship',
                'identity_number',
                'passport_number',
                'license_levels',
            ]);
        });

        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn([
                'mailing_address',
                'training_address',
                'deed_file_path',
                'statement_file_path',
            ]);
        });
    }
};
