<?php

return [
    /*
     * Twig configuration
     */
    'twig' => [
        'extension' => 'twig',
        'environment' => [
            'debug' => env('APP_DEBUG', false),
            'cache' => storage_path('framework/views/twig'),
            'auto_reload' => true,
            'autoescape' => 'html',
        ],
    ],

    /*
     * Extensions to enable
     */
    'extensions' => [
        'enabled' => [
            // Laravel extensions would go here
        ],
    ],
];