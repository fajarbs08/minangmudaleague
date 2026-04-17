<?php

use App\Models\InformationResource;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('information_resources', function (Blueprint $table) {
            $table->string('visibility', 20)
                ->default(InformationResource::VISIBILITY_CLUB)
                ->after('file_mime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('information_resources', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
};
