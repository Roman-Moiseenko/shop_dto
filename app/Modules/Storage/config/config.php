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

    /**
     * Module name
     */
    'name' => 'Storage',

    'architecture_pattern' => '',


    //Нарезки, конфигурация
    //Настройки для копий
    'cache' => [
        'disk' => env('CACHE_DISK', 'public'),
        'max_width' => null,
        'max_height' => null,
        'watermark' => true,
    ],
    //Водяной знак, общие настройки
    'watermark' => [
        'disk' => 'local',
        // путь к файлу водяного знака (относительно диска storage.local.disk)
        'path' => 'watermark/watermark.png',

        // доля от ширины изображения, которую займёт знак (0.05 = 5%)
        'ratio' => 0.1,

        // отступ от края в пикселях
        'offset' => 20,

        // положение: bottom-right, bottom-left, top-right, top-left
        'position' => 'bottom-right',
    ],
    //Уменьшенные копии
    'thumbs' => [
        // entity_type -> type -> список нарезок
        //Модуль => Сущности

        'catalog.product' => [
            'image' => [
                'card' => ['width' => 600, 'height' => 600, 'fit' => true, 'watermark' => true],
                'list' => ['width' => 150, 'height' => 150],
                'thumb' => ['width' => 80, 'height' => 80, 'fit' => true],
            ],
            'gallery' => [
                'thumb' => ['width' => 120, 'height' => 120],
            ],
        ],
        'catalog.category' => [
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
        'disk' => env('STORAGE_DISK', 'local'),
        'upload_path' => 'uploads',
        'cache_path' => 'cache',
    ],
];
