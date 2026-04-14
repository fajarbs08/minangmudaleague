<?php

return [
    'competition_name' => env('ID_CARDS_COMPETITION_NAME', 'Minang Muda League'),
    'website' => env('ID_CARDS_WEBSITE', 'minangmudaleague.com'),
    'organizer' => env('ID_CARDS_ORGANIZER', 'Minang Muda League Organizer'),
    'chrome_path' => env('ID_CARDS_CHROME_PATH', '/usr/bin/google-chrome'),
    'node_binary' => env('ID_CARDS_NODE_BINARY', '/usr/bin/node'),
    'node_modules_path' => env('ID_CARDS_NODE_MODULES_PATH', storage_path('app/id-card-node/node_modules')),
    'timeout' => (int) env('ID_CARDS_TIMEOUT', 90),
    'wait_until' => env('ID_CARDS_WAIT_UNTIL', 'load'),
    'no_sandbox' => env('ID_CARDS_NO_SANDBOX', true),
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
