<?php

namespace Database\Seeders;

use App\Models\AgeGroup;

class AgeGroupSeeder extends AbstractDemoSeeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'U10', 'name' => 'U-10', 'min_age' => 8, 'max_age' => 10],
            ['code' => 'U12', 'name' => 'U-12', 'min_age' => 10, 'max_age' => 12],
            ['code' => 'U14', 'name' => 'U-14', 'min_age' => 12, 'max_age' => 14],
            ['code' => 'U16', 'name' => 'U-16', 'min_age' => 14, 'max_age' => 16],
        ] as $group) {
            AgeGroup::updateOrCreate(
                ['code' => $group['code']],
                $group + ['is_active' => true]
            );
        }
    }
}
