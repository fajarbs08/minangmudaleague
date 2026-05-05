<?php

return [
    'competition_name' => env('ID_CARDS_COMPETITION_NAME', 'Liga Anak Piaman Laweh'),
    'website' => env('ID_CARDS_WEBSITE', 'ligaanakpiamanlaweh.com'),
    'organizer' => env('ID_CARDS_ORGANIZER', 'Liga Anak Piaman Laweh Organizer'),
    'card' => [
        'width_mm' => 85.6,
        'height_mm' => 54.0,
    ],
    'page' => [
        'width_mm' => 210.0,
        'height_mm' => 297.0,
    ],
    'assets' => [
        'league_logo_light' => public_path('images/logo-white.png'),
        'league_logo_dark' => public_path('images/logo-dark.png'),
        'league_watermark' => public_path('images/logo-full-transparent.png'),
        'photo_fallback' => public_path('images/users/avatar-1.jpg'),
        'club_fallback' => public_path('images/logo-dark.png'),
    ],
];
