<?php

// Configuration propre au projet Legna Premium Italia
return [

    // Premier compte administrateur (AdminUserSeeder) — valeurs dans .env, jamais dans le code
    'admin' => [
        'name' => env('ADMIN_NAME', 'Amministratore'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    // Sel secret pour anonymiser le comptage des vues (R-STAT-02, sprint 10)
    'stats_hash_salt' => env('STATS_HASH_SALT'),

    // Clé de cache des paramètres (SettingsService)
    'settings_cache_key' => 'legna.settings',
];
