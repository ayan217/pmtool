<?php

return [
    'reminder_hours' => [1, 2, 3, 4, 6, 12, 24],
    'default_reminder_hours' => 3,
    'per_page_options' => [25, 50, 100],
    'default_per_page' => 25,

    'seed' => [
        'name' => env('SEED_USER_NAME', 'Ayan'),
        'email' => env('SEED_USER_EMAIL', 'ayan@example.com'),
        'password' => env('SEED_USER_PASSWORD', 'password'),
    ],
];
