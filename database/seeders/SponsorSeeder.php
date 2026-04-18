<?php

namespace Database\Seeders;

use Illuminate\Support\Str;

class SponsorSeeder extends AbstractDemoSeeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Bumi Sport', 'short_name' => 'Bumi', 'tier' => 'gold', 'sort_order' => 1],
            ['name' => 'Piaman Print', 'short_name' => 'Print', 'tier' => 'silver', 'sort_order' => 2],
            ['name' => 'Nagari Tech', 'short_name' => 'Tech', 'tier' => 'bronze', 'sort_order' => 3],
        ] as $sponsor) {
            $logoPath = $this->seedDemoImage(Str::slug($sponsor['name']).'.svg', $sponsor['name']);

            $this->upsertSponsor(
                ['name' => $sponsor['name']],
                [
                    'short_name' => $sponsor['short_name'],
                    'logo_path' => $logoPath,
                    'website_url' => 'https://example.com/'.$sponsor['short_name'],
                    'tier' => $sponsor['tier'],
                    'sort_order' => $sponsor['sort_order'],
                    'is_published' => true,
                ]
            );
        }
    }
}
