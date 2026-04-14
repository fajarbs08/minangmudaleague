<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_age_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('age_group_id')->constrained()->cascadeOnDelete();
            $table->string('season')->nullable();
            $table->unsignedTinyInteger('jersey_number')->nullable();
            $table->string('position')->nullable();
            $table->string('registration_status', 20)->nullable();
            $table->timestamp('status_date')->nullable();
            $table->timestamps();

            $table->unique(['player_id', 'age_group_id']);
        });

        $players = DB::table('players')
            ->select([
                'id',
                'primary_age_group_id as age_group_id',
                'jersey_number',
                'position',
                'verification_status as registration_status',
                DB::raw('COALESCE(reviewed_at, submitted_at, updated_at, created_at) as status_date'),
                'created_at',
                'updated_at',
            ])
            ->whereNotNull('primary_age_group_id')
            ->get();

        foreach ($players as $player) {
            DB::table('player_age_groups')->updateOrInsert(
                [
                    'player_id' => $player->id,
                    'age_group_id' => $player->age_group_id,
                ],
                [
                    'season' => (string) date('Y'),
                    'jersey_number' => $player->jersey_number,
                    'position' => $player->position,
                    'registration_status' => $player->registration_status,
                    'status_date' => $player->status_date,
                    'created_at' => $player->created_at,
                    'updated_at' => $player->updated_at,
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('player_age_groups');
    }
};
