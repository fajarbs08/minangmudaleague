<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('official_age_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('official_id')->constrained()->cascadeOnDelete();
            $table->foreignId('age_group_id')->constrained()->cascadeOnDelete();
            $table->string('season')->nullable();
            $table->string('role')->nullable();
            $table->string('license_levels')->nullable();
            $table->string('registration_status', 20)->nullable();
            $table->timestamp('status_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['official_id', 'age_group_id']);
        });

        $officials = DB::table('officials')
            ->select([
                'id',
                'age_group_id',
                'role',
                'license_levels',
                'verification_status as registration_status',
                DB::raw('COALESCE(reviewed_at, submitted_at, updated_at, created_at) as status_date'),
                'created_at',
                'updated_at',
            ])
            ->whereNotNull('age_group_id')
            ->get();

        foreach ($officials as $official) {
            DB::table('official_age_groups')->updateOrInsert(
                [
                    'official_id' => $official->id,
                    'age_group_id' => $official->age_group_id,
                ],
                [
                    'season' => (string) date('Y'),
                    'role' => $official->role,
                    'license_levels' => $official->license_levels,
                    'registration_status' => $official->registration_status,
                    'status_date' => $official->status_date,
                    'created_at' => $official->created_at,
                    'updated_at' => $official->updated_at,
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('official_age_groups');
    }
};
