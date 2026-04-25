<?php

return [
    'proxies' => array_values(array_filter(array_map(
        static fn (string $proxy) => trim($proxy),
        explode(',', (string) env('TRUSTED_PROXIES', ''))
    ))),
];
