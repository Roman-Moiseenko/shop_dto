<?php

/**
 * Configuration file for Storage module
 *
 * This file contains module-specific settings that will be automatically
 * loaded and merged with the application configuration.
 *
 * Access your configuration values using:
 * config('storage.your_key')
 *
 * @package Storage Module
 * @author Easy Module Generator
 * @version 1.0.0
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Module Information
    |--------------------------------------------------------------------------
    |
    | Basic module identification and status configuration.
    |
    */

    /**
     * Module name
     */
    'name' => 'Storage',

    /**
     * Architecture pattern used for this module
     * Leave empty ('') to use default configuration
     * Available in v2.0: 'custom', 'laravel', 'mvc', 'clean-architecture', 'ddd', 'event-sourcing', etc.
     */
    'architecture_pattern' => '',

    /*
    |--------------------------------------------------------------------------
    | Custom Module Configuration
    |--------------------------------------------------------------------------
    |
    | Add your own configuration values below. These will be accessible
    | throughout your module using the config() helper function.
    |
    |
    */
    //Нарезки, конфигурация
    'thumbs' => [
        // entity_type -> type -> список нарезок
        //Модуль.Сущность
        'catalog.product' => [
            'image' => [
                'card' => ['width' => 600, 'height' => 600],
                'list' => ['width' => 150, 'height' => 150],
                'thumb' => ['width' => 80, 'height' => 80],
            ],
            'gallery' => [
                'thumb' => ['width' => 120, 'height' => 120],
            ],
        ],
        'catalog_category' => [
            'image' => [
                'card' => ['width' => 600, 'height' => 600],
                'list' => ['width' => 150, 'height' => 150],
            ],
            'icon' => [
                'thumb' => ['width' => 80, 'height' => 80],
            ],
        ],
        'auth.client' => [
            'image' => [
                'avatar' => ['width' => 120, 'height' => 120],
            ],
        ],
        'auth.staff' => [
            'image' => [
                'avatar' => ['width' => 120, 'height' => 120],
            ],
        ],
        'auth.freelance' => [
            'image' => [
                'avatar' => ['width' => 120, 'height' => 120],
            ],
        ],
    ],
    'local' => [
        'disk' => env('STORAGE_DISK', 'public'),
        'upload_path' => 'uploads',
        'cache_path' => 'cache',
    ],
];
