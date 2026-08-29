<?php

return [
    'laravel/breeze' => [
        'providers' => [
            0 => 'Laravel\\Breeze\\BreezeServiceProvider',
        ],
    ],
    'laravel/pail' => [
        'providers' => [
            0 => 'Laravel\\Pail\\PailServiceProvider',
        ],
    ],
    'laravel/sail' => [
        'providers' => [
            0 => 'Laravel\\Sail\\SailServiceProvider',
        ],
    ],
    'laravel/sanctum' => [
        'providers' => [
            0 => 'Laravel\\Sanctum\\SanctumServiceProvider',
        ],
    ],
    'laravel/tinker' => [
        'providers' => [
            0 => 'Laravel\\Tinker\\TinkerServiceProvider',
        ],
    ],
    'nesbot/carbon' => [
        'providers' => [
            0 => 'Carbon\\Laravel\\ServiceProvider',
        ],
    ],
    'nunomaduro/collision' => [
        'providers' => [
            0 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
        ],
    ],
    'nunomaduro/termwind' => [
        'providers' => [
            0 => 'Termwind\\Laravel\\TermwindServiceProvider',
        ],
    ],
    'nwidart/laravel-modules' => [
        'aliases' => [
            'Module' => 'Nwidart\\Modules\\Facades\\Module',
        ],
        'providers' => [
            0 => 'Nwidart\\Modules\\LaravelModulesServiceProvider',
        ],
    ],
    'yajra/laravel-datatables-oracle' => [
        'aliases' => [
            'DataTables' => 'Yajra\\DataTables\\Facades\\DataTables',
        ],
        'providers' => [
            0 => 'Yajra\\DataTables\\DataTablesServiceProvider',
        ],
    ],
];
