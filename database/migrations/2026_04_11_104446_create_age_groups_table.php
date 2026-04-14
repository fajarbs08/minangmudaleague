<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('age_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->unsignedTinyInteger('min_age')->nullable();
            $table->unsignedTinyInteger('max_age')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('age_groups')->insert([
            ['name' => 'U-10', 'code' => 'U10', 'min_age' => 8, 'max_age' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'U-11', 'code' => 'U11', 'min_age' => 9, 'max_age' => 11, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'U-12', 'code' => 'U12', 'min_age' => 10, 'max_age' => 12, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'U-13', 'code' => 'U13', 'min_age' => 11, 'max_age' => 13, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'U-14', 'code' => 'U14', 'min_age' => 12, 'max_age' => 14, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'U-15', 'code' => 'U15', 'min_age' => 13, 'max_age' => 15, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'U-16', 'code' => 'U16', 'min_age' => 14, 'max_age' => 16, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'U-17', 'code' => 'U17', 'min_age' => 15, 'max_age' => 17, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'U-18', 'code' => 'U18', 'min_age' => 16, 'max_age' => 18, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('age_groups');
    }
};
