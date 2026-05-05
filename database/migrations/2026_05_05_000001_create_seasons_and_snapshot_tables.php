<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->string('status', 20)->default('draft');
            $table->boolean('is_active')->nullable()->unique();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        $currentYear = date('Y');
        DB::table('seasons')->insert([
            'name' => 'Musim '.$currentYear,
            'slug' => 'musim-'.$currentYear,
            'starts_at' => $currentYear.'-01-01',
            'ends_at' => $currentYear.'-12-31',
            'status' => 'active',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('season_clubs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('club_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('short_name', 50)->nullable();
            $table->string('manager_name')->nullable();
            $table->string('manager_title')->nullable();
            $table->string('zone')->nullable();
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('statement_file_path')->nullable();
            $table->text('address')->nullable();
            $table->text('training_address')->nullable();
            $table->text('notes')->nullable();
            $table->string('verification_status', 20)->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('snapshot_source_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['season_id', 'club_id']);
            $table->index(['season_id', 'user_id']);
            $table->index(['season_id', 'name']);
        });

        Schema::create('season_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('season_club_id')->nullable()->constrained('season_clubs')->nullOnDelete();
            $table->foreignId('club_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('player_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('primary_age_group_id')->nullable()->constrained('age_groups')->nullOnDelete();
            $table->string('name');
            $table->string('mother_name')->nullable();
            $table->string('school_name')->nullable();
            $table->unsignedTinyInteger('jersey_number')->nullable();
            $table->string('position')->nullable();
            $table->string('citizenship', 20)->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->unsignedSmallInteger('weight_kg')->nullable();
            $table->string('dominant_foot')->nullable();
            $table->boolean('is_captain')->default(false);
            $table->string('photo_path')->nullable();
            $table->string('diploma_file_path')->nullable();
            $table->string('report_file_path')->nullable();
            $table->string('birth_certificate_file_path')->nullable();
            $table->string('family_card_file_path')->nullable();
            $table->text('notes')->nullable();
            $table->string('verification_status', 20)->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->json('registered_age_group_ids')->nullable();
            $table->json('age_registration_snapshot')->nullable();
            $table->timestamp('snapshot_source_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['season_id', 'player_id']);
            $table->index(['season_id', 'season_club_id']);
            $table->index(['season_id', 'club_id']);
        });

        Schema::create('season_officials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('season_club_id')->nullable()->constrained('season_clubs')->nullOnDelete();
            $table->foreignId('club_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('official_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('age_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('citizenship', 20)->nullable();
            $table->string('identity_number')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('license_number')->nullable();
            $table->string('license_levels')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('license_file_path')->nullable();
            $table->string('identity_file_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->string('verification_status', 20)->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->json('registered_age_group_ids')->nullable();
            $table->json('age_registration_snapshot')->nullable();
            $table->timestamp('snapshot_source_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['season_id', 'official_id']);
            $table->index(['season_id', 'season_club_id']);
            $table->index(['season_id', 'club_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season_officials');
        Schema::dropIfExists('season_players');
        Schema::dropIfExists('season_clubs');
        Schema::dropIfExists('seasons');
    }
};
