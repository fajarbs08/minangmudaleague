<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('player_age_groups', 'season_id')) {
            Schema::table('player_age_groups', function (Blueprint $table) {
                $table->foreignId('season_id')->nullable()->after('age_group_id')->constrained()->nullOnDelete();
            });
        }

        if (! $this->hasIndex('player_age_groups', 'player_age_groups_player_id_index')) {
            Schema::table('player_age_groups', function (Blueprint $table) {
                $table->index('player_id', 'player_age_groups_player_id_index');
            });
        }

        if (! $this->hasIndex('player_age_groups', 'player_age_groups_age_group_id_index')) {
            Schema::table('player_age_groups', function (Blueprint $table) {
                $table->index('age_group_id', 'player_age_groups_age_group_id_index');
            });
        }

        if (! $this->hasIndex('player_age_groups', 'player_age_groups_season_id_age_group_id_index')) {
            Schema::table('player_age_groups', function (Blueprint $table) {
                $table->index(['season_id', 'age_group_id']);
            });
        }

        if (! Schema::hasColumn('official_age_groups', 'season_id')) {
            Schema::table('official_age_groups', function (Blueprint $table) {
                $table->foreignId('season_id')->nullable()->after('age_group_id')->constrained()->nullOnDelete();
            });
        }

        if (! $this->hasIndex('official_age_groups', 'official_age_groups_official_id_index')) {
            Schema::table('official_age_groups', function (Blueprint $table) {
                $table->index('official_id', 'official_age_groups_official_id_index');
            });
        }

        if (! $this->hasIndex('official_age_groups', 'official_age_groups_age_group_id_index')) {
            Schema::table('official_age_groups', function (Blueprint $table) {
                $table->index('age_group_id', 'official_age_groups_age_group_id_index');
            });
        }

        if (! $this->hasIndex('official_age_groups', 'official_age_groups_season_id_age_group_id_index')) {
            Schema::table('official_age_groups', function (Blueprint $table) {
                $table->index(['season_id', 'age_group_id']);
            });
        }

        if (! Schema::hasColumn('match_schedules', 'season_id')) {
            Schema::table('match_schedules', function (Blueprint $table) {
                $table->foreignId('season_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('match_schedules', 'club_a_season_id')) {
            Schema::table('match_schedules', function (Blueprint $table) {
                $table->foreignId('club_a_season_id')->nullable()->after('club_a_id')->constrained('season_clubs')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('match_schedules', 'club_b_season_id')) {
            Schema::table('match_schedules', function (Blueprint $table) {
                $table->foreignId('club_b_season_id')->nullable()->after('club_b_id')->constrained('season_clubs')->nullOnDelete();
            });
        }

        if (! $this->hasIndex('match_schedules', 'match_schedules_season_id_age_group_id_match_date_index')) {
            Schema::table('match_schedules', function (Blueprint $table) {
                $table->index(['season_id', 'age_group_id', 'match_date']);
            });
        }

        if (! Schema::hasColumn('lineup_lists', 'season_id')) {
            Schema::table('lineup_lists', function (Blueprint $table) {
                $table->foreignId('season_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('lineup_lists', 'season_club_id')) {
            Schema::table('lineup_lists', function (Blueprint $table) {
                $table->foreignId('season_club_id')->nullable()->after('club_id')->constrained('season_clubs')->nullOnDelete();
            });
        }

        if (! $this->hasIndex('lineup_lists', 'lineup_lists_season_id_club_id_index')) {
            Schema::table('lineup_lists', function (Blueprint $table) {
                $table->index(['season_id', 'club_id']);
            });
        }

        if (! $this->hasIndex('lineup_lists', 'lineup_lists_season_id_match_id_index')) {
            Schema::table('lineup_lists', function (Blueprint $table) {
                $table->index(['season_id', 'match_id']);
            });
        }

        if (! Schema::hasColumn('match_goals', 'season_id')) {
            Schema::table('match_goals', function (Blueprint $table) {
                $table->foreignId('season_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('match_goals', 'season_club_id')) {
            Schema::table('match_goals', function (Blueprint $table) {
                $table->foreignId('season_club_id')->nullable()->after('club_id')->constrained('season_clubs')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('match_goals', 'season_player_id')) {
            Schema::table('match_goals', function (Blueprint $table) {
                $table->foreignId('season_player_id')->nullable()->after('player_id')->constrained('season_players')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('match_goals', 'assist_season_player_id')) {
            Schema::table('match_goals', function (Blueprint $table) {
                $table->foreignId('assist_season_player_id')->nullable()->after('assist_player_id')->constrained('season_players')->nullOnDelete();
            });
        }

        if (! $this->hasIndex('match_goals', 'match_goals_season_id_match_id_index')) {
            Schema::table('match_goals', function (Blueprint $table) {
                $table->index(['season_id', 'match_id']);
            });
        }

        if (! Schema::hasColumn('lineup_list_player', 'season_player_id')) {
            Schema::table('lineup_list_player', function (Blueprint $table) {
                $table->foreignId('season_player_id')->nullable()->after('player_id')->constrained('season_players')->nullOnDelete();
            });
        }

        $season = $this->activeSeason();
        $seasonName = $season->name;
        $seasonId = (int) $season->id;

        DB::table('player_age_groups')
            ->whereNull('season_id')
            ->update([
                'season_id' => $seasonId,
                'season' => DB::raw("COALESCE(season, '".str_replace("'", "''", $seasonName)."')"),
            ]);

        DB::table('official_age_groups')
            ->whereNull('season_id')
            ->update([
                'season_id' => $seasonId,
                'season' => DB::raw("COALESCE(season, '".str_replace("'", "''", $seasonName)."')"),
            ]);

        $this->backfillSeasonClubs($seasonId);
        $seasonClubIdsByClubId = DB::table('season_clubs')
            ->where('season_id', $seasonId)
            ->pluck('id', 'club_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->backfillSeasonPlayers($seasonId, $seasonClubIdsByClubId);
        $this->backfillSeasonOfficials($seasonId, $seasonClubIdsByClubId);

        $seasonPlayerIdsByPlayerId = DB::table('season_players')
            ->where('season_id', $seasonId)
            ->pluck('id', 'player_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        DB::table('match_schedules')->orderBy('id')->chunkById(200, function ($matches) use ($seasonId, $seasonClubIdsByClubId) {
            foreach ($matches as $match) {
                DB::table('match_schedules')
                    ->where('id', $match->id)
                    ->update([
                        'season_id' => $seasonId,
                        'club_a_season_id' => $seasonClubIdsByClubId[$match->club_a_id] ?? null,
                        'club_b_season_id' => $seasonClubIdsByClubId[$match->club_b_id] ?? null,
                    ]);
            }
        });

        DB::table('lineup_lists')->orderBy('id')->chunkById(200, function ($lineups) use ($seasonId, $seasonClubIdsByClubId) {
            foreach ($lineups as $lineup) {
                DB::table('lineup_lists')
                    ->where('id', $lineup->id)
                    ->update([
                        'season_id' => $seasonId,
                        'season_club_id' => $seasonClubIdsByClubId[$lineup->club_id] ?? null,
                    ]);
            }
        });

        DB::table('match_goals')->orderBy('id')->chunkById(200, function ($goals) use ($seasonId, $seasonClubIdsByClubId, $seasonPlayerIdsByPlayerId) {
            foreach ($goals as $goal) {
                DB::table('match_goals')
                    ->where('id', $goal->id)
                    ->update([
                        'season_id' => $seasonId,
                        'season_club_id' => $seasonClubIdsByClubId[$goal->club_id] ?? null,
                        'season_player_id' => $seasonPlayerIdsByPlayerId[$goal->player_id] ?? null,
                        'assist_season_player_id' => $goal->assist_player_id ? ($seasonPlayerIdsByPlayerId[$goal->assist_player_id] ?? null) : null,
                    ]);
            }
        });

        DB::table('lineup_list_player')->orderBy('id')->chunkById(200, function ($entries) use ($seasonPlayerIdsByPlayerId) {
            foreach ($entries as $entry) {
                DB::table('lineup_list_player')
                    ->where('id', $entry->id)
                    ->update([
                        'season_player_id' => $seasonPlayerIdsByPlayerId[$entry->player_id] ?? null,
                    ]);
            }
        });

        if ($this->hasIndex('player_age_groups', 'player_age_groups_player_id_age_group_id_unique')) {
            Schema::table('player_age_groups', function (Blueprint $table) {
                $table->dropUnique('player_age_groups_player_id_age_group_id_unique');
            });
        }

        if (! $this->hasIndex('player_age_groups', 'player_age_groups_player_age_season_unique')) {
            Schema::table('player_age_groups', function (Blueprint $table) {
                $table->unique(['player_id', 'age_group_id', 'season_id'], 'player_age_groups_player_age_season_unique');
            });
        }

        if ($this->hasIndex('official_age_groups', 'official_age_groups_official_id_age_group_id_unique')) {
            Schema::table('official_age_groups', function (Blueprint $table) {
                $table->dropUnique('official_age_groups_official_id_age_group_id_unique');
            });
        }

        if (! $this->hasIndex('official_age_groups', 'official_age_groups_official_age_season_unique')) {
            Schema::table('official_age_groups', function (Blueprint $table) {
                $table->unique(['official_id', 'age_group_id', 'season_id'], 'official_age_groups_official_age_season_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('official_age_groups', function (Blueprint $table) {
            $table->dropUnique('official_age_groups_official_age_season_unique');
            $table->dropIndex('official_age_groups_official_id_index');
            $table->dropIndex('official_age_groups_age_group_id_index');
            $table->dropIndex(['season_id', 'age_group_id']);
            $table->dropConstrainedForeignId('season_id');
            $table->unique(['official_id', 'age_group_id']);
        });

        Schema::table('player_age_groups', function (Blueprint $table) {
            $table->dropUnique('player_age_groups_player_age_season_unique');
            $table->dropIndex('player_age_groups_player_id_index');
            $table->dropIndex('player_age_groups_age_group_id_index');
            $table->dropIndex(['season_id', 'age_group_id']);
            $table->dropConstrainedForeignId('season_id');
            $table->unique(['player_id', 'age_group_id']);
        });

        Schema::table('lineup_list_player', function (Blueprint $table) {
            $table->dropConstrainedForeignId('season_player_id');
        });

        Schema::table('match_goals', function (Blueprint $table) {
            $table->dropIndex(['season_id', 'match_id']);
            $table->dropConstrainedForeignId('assist_season_player_id');
            $table->dropConstrainedForeignId('season_player_id');
            $table->dropConstrainedForeignId('season_club_id');
            $table->dropConstrainedForeignId('season_id');
        });

        Schema::table('lineup_lists', function (Blueprint $table) {
            $table->dropIndex(['season_id', 'club_id']);
            $table->dropIndex(['season_id', 'match_id']);
            $table->dropConstrainedForeignId('season_club_id');
            $table->dropConstrainedForeignId('season_id');
        });

        Schema::table('match_schedules', function (Blueprint $table) {
            $table->dropIndex(['season_id', 'age_group_id', 'match_date']);
            $table->dropConstrainedForeignId('club_b_season_id');
            $table->dropConstrainedForeignId('club_a_season_id');
            $table->dropConstrainedForeignId('season_id');
        });
    }

    private function activeSeason(): object
    {
        $season = DB::table('seasons')
            ->where('is_active', true)
            ->first()
            ?? DB::table('seasons')->orderByDesc('id')->first();

        if (! $season) {
            return (object) [
                'id' => 1,
                'name' => 'Musim '.date('Y'),
            ];
        }

        return $season;
    }

    private function backfillSeasonClubs(int $seasonId): void
    {
        DB::table('clubs')->orderBy('id')->chunkById(100, function ($clubs) use ($seasonId) {
            foreach ($clubs as $club) {
                DB::table('season_clubs')->updateOrInsert(
                    [
                        'season_id' => $seasonId,
                        'club_id' => $club->id,
                    ],
                    [
                        'user_id' => $club->user_id,
                        'name' => $club->name,
                        'short_name' => $club->short_name,
                        'manager_name' => $club->manager_name,
                        'manager_title' => $club->manager_title,
                        'zone' => $club->zone,
                        'founded_year' => $club->founded_year,
                        'logo_url' => $club->logo_url,
                        'statement_file_path' => $club->statement_file_path,
                        'address' => $club->address,
                        'training_address' => $club->training_address,
                        'notes' => $club->notes,
                        'verification_status' => $club->verification_status,
                        'verification_notes' => $club->verification_notes,
                        'submitted_at' => $club->submitted_at,
                        'reviewed_by' => $club->reviewed_by,
                        'reviewed_at' => $club->reviewed_at,
                        'snapshot_source_updated_at' => $club->updated_at,
                        'created_at' => $club->created_at ?? now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }

    private function backfillSeasonPlayers(int $seasonId, array $seasonClubIdsByClubId): void
    {
        DB::table('players')->orderBy('id')->chunkById(100, function ($players) use ($seasonId, $seasonClubIdsByClubId) {
            $playerIds = $players->pluck('id')->all();
            $registrations = DB::table('player_age_groups')
                ->where('season_id', $seasonId)
                ->whereIn('player_id', $playerIds)
                ->orderBy('age_group_id')
                ->get()
                ->groupBy('player_id');

            foreach ($players as $player) {
                $registrationRows = collect($registrations->get($player->id, collect()));

                DB::table('season_players')->updateOrInsert(
                    [
                        'season_id' => $seasonId,
                        'player_id' => $player->id,
                    ],
                    [
                        'season_club_id' => $seasonClubIdsByClubId[$player->club_id] ?? null,
                        'club_id' => $player->club_id,
                        'primary_age_group_id' => $player->primary_age_group_id,
                        'name' => $player->name,
                        'mother_name' => $player->mother_name,
                        'school_name' => $player->school_name,
                        'jersey_number' => $player->jersey_number,
                        'position' => $player->position,
                        'citizenship' => $player->citizenship,
                        'birth_place' => $player->birth_place,
                        'birth_date' => $player->birth_date,
                        'height_cm' => $player->height_cm,
                        'weight_kg' => $player->weight_kg,
                        'dominant_foot' => $player->dominant_foot,
                        'is_captain' => $player->is_captain,
                        'photo_path' => $player->photo_path,
                        'diploma_file_path' => $player->diploma_file_path,
                        'report_file_path' => $player->report_file_path,
                        'birth_certificate_file_path' => $player->birth_certificate_file_path,
                        'family_card_file_path' => $player->family_card_file_path,
                        'notes' => $player->notes,
                        'verification_status' => $player->verification_status,
                        'verification_notes' => $player->verification_notes,
                        'submitted_at' => $player->submitted_at,
                        'reviewed_by' => $player->reviewed_by,
                        'reviewed_at' => $player->reviewed_at,
                        'registered_age_group_ids' => json_encode($registrationRows->pluck('age_group_id')->values()->all()),
                        'age_registration_snapshot' => json_encode($registrationRows->map(fn ($registration) => [
                            'age_group_id' => $registration->age_group_id,
                            'season' => $registration->season,
                            'season_id' => $registration->season_id,
                            'jersey_number' => $registration->jersey_number,
                            'position' => $registration->position,
                            'registration_status' => $registration->registration_status,
                            'status_date' => $registration->status_date,
                            'notes' => $registration->notes,
                            'is_starter' => (bool) ($registration->is_starter ?? false),
                            'is_substitute' => (bool) ($registration->is_substitute ?? false),
                        ])->values()->all()),
                        'snapshot_source_updated_at' => $player->updated_at,
                        'created_at' => $player->created_at ?? now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }

    private function backfillSeasonOfficials(int $seasonId, array $seasonClubIdsByClubId): void
    {
        DB::table('officials')->orderBy('id')->chunkById(100, function ($officials) use ($seasonId, $seasonClubIdsByClubId) {
            $officialIds = $officials->pluck('id')->all();
            $registrations = DB::table('official_age_groups')
                ->where('season_id', $seasonId)
                ->whereIn('official_id', $officialIds)
                ->orderBy('age_group_id')
                ->get()
                ->groupBy('official_id');

            foreach ($officials as $official) {
                $registrationRows = collect($registrations->get($official->id, collect()));

                DB::table('season_officials')->updateOrInsert(
                    [
                        'season_id' => $seasonId,
                        'official_id' => $official->id,
                    ],
                    [
                        'season_club_id' => $seasonClubIdsByClubId[$official->club_id] ?? null,
                        'club_id' => $official->club_id,
                        'age_group_id' => $official->age_group_id,
                        'name' => $official->name,
                        'role' => $official->role,
                        'phone' => $official->phone,
                        'email' => $official->email,
                        'birth_place' => $official->birth_place,
                        'citizenship' => $official->citizenship,
                        'identity_number' => $official->identity_number,
                        'birth_date' => $official->birth_date,
                        'license_number' => $official->license_number,
                        'license_levels' => $official->license_levels,
                        'photo_path' => $official->photo_path,
                        'license_file_path' => $official->license_file_path,
                        'identity_file_path' => $official->identity_file_path,
                        'is_active' => $official->is_active,
                        'notes' => $official->notes,
                        'verification_status' => $official->verification_status,
                        'verification_notes' => $official->verification_notes,
                        'submitted_at' => $official->submitted_at,
                        'reviewed_by' => $official->reviewed_by,
                        'reviewed_at' => $official->reviewed_at,
                        'registered_age_group_ids' => json_encode($registrationRows->pluck('age_group_id')->values()->all()),
                        'age_registration_snapshot' => json_encode($registrationRows->map(fn ($registration) => [
                            'age_group_id' => $registration->age_group_id,
                            'season' => $registration->season,
                            'season_id' => $registration->season_id,
                            'role' => $registration->role,
                            'license_levels' => $registration->license_levels,
                            'registration_status' => $registration->registration_status,
                            'status_date' => $registration->status_date,
                            'notes' => $registration->notes,
                        ])->values()->all()),
                        'snapshot_source_updated_at' => $official->updated_at,
                        'created_at' => $official->created_at ?? now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $result = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$indexName]);

        return ! empty($result);
    }
};
