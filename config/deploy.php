<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Deployment Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Laravel Deploy package.
    | Most settings can be overridden via .env file.
    |
    */

    'git' => [
        'remote' => env('GIT_REMOTE', 'origin'),
        'repository_url' => env('GIT_REPOSITORY_URL'),
    ],

    'server' => [
        'url' => env('DEPLOY_SERVER_URL'),
        'token' => env('DEPLOY_TOKEN'),
        'timeout' => env('DEPLOY_TIMEOUT', 300),
    ],

    'build' => [
        'npm_timeout' => env('NPM_TIMEOUT', 600),
        'auto_install' => env('DEPLOY_AUTO_NPM_INSTALL', true),
    ],

    'logging' => [
        'enabled' => env('DEPLOY_LOGGING', true),
        'path' => storage_path('logs/deploy.log'),
    ],
];

