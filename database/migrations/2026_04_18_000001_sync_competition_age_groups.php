<?php

use App\Models\AgeGroup;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            ['code' => 'U10', 'name' => 'U-10', 'min_age' => 8, 'max_age' => 10],
            ['code' => 'U12', 'name' => 'U-12', 'min_age' => 10, 'max_age' => 12],
            ['code' => 'U14', 'name' => 'U-14', 'min_age' => 12, 'max_age' => 14],
            ['code' => 'U16', 'name' => 'U-16', 'min_age' => 14, 'max_age' => 16],
        ] as $group) {
            AgeGroup::query()->updateOrCreate(
                ['code' => $group['code']],
                [
                    'name' => $group['name'],
                    'min_age' => $group['min_age'],
                    'max_age' => $group['max_age'],
                    'is_active' => true,
                ]
            );
        }

        AgeGroup::query()
            ->whereNotIn('code', AgeGroup::COMPETITION_CODES)
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        AgeGroup::query()->update(['is_active' => true]);
    }
};
