<?php

use App\Models\InformationResource;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('information_resources')
            ->where('visibility', InformationResource::VISIBILITY_CLUB)
            ->update([
                'visibility' => InformationResource::VISIBILITY_PUBLIC,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
