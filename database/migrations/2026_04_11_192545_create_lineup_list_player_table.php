<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lineup_list_player', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lineup_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20);
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();

            $table->unique(['lineup_list_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lineup_list_player');
    }
};
