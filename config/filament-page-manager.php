<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    |
    | Configure the table names for pages and regions.
    |
    */
    'tables' => [
        'pages' => 'fpm_pages',
        'regions' => 'fpm_regions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Configure the models to use for pages and regions.
    | You can extend the default models and override them here.
    |
    */
    'models' => [
        'page' => \IamGerwin\FilamentPageManager\Models\Page::class,
        'region' => \IamGerwin\FilamentPageManager\Models\Region::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Filament Resources
    |--------------------------------------------------------------------------
    |
    | Configure the Filament resources for pages and regions.
    | You can extend the default resources and override them here.
    |
    */
    'resources' => [
        'page' => \IamGerwin\FilamentPageManager\Filament\Resources\PageResource::class,
        'region' => \IamGerwin\FilamentPageManager\Filament\Resources\RegionResource::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    |
    | Register your page and region templates here.
    | Templates should extend the AbstractTemplate class.
    |
    */
    'templates' => [
        // Example:
        // \App\PageTemplates\HomePageTemplate::class,
        // \App\PageTemplates\AboutPageTemplate::class,
        // \App\RegionTemplates\HeroRegionTemplate::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | Configure the available locales for your application.
    | These will be used for translatable fields.
    |
    */
    'locales' => [
        'en' => 'English',
        // 'es' => 'Spanish',
        // 'fr' => 'French',
        // 'de' => 'German',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | The default locale to use when none is specified.
    |
    */
    'default_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | SEO Configuration
    |--------------------------------------------------------------------------
    |
    | Configure SEO field settings.
    |
    */
    'seo' => [
        'enabled' => true,
        'fields' => [
            'title' => [
                'label' => 'SEO Title',
                'type' => 'text',
                'required' => false,
                'maxLength' => 60,
            ],
            'description' => [
                'label' => 'SEO Description',
                'type' => 'textarea',
                'required' => false,
                'maxLength' => 160,
            ],
            'keywords' => [
                'label' => 'SEO Keywords',
                'type' => 'text',
                'required' => false,
                'helperText' => 'Comma-separated keywords',
            ],
            'image' => [
                'label' => 'OG Image',
                'type' => 'image',
                'required' => false,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Page Path Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how page paths are generated.
    | Note: Set base_url in your .env file as APP_URL or directly here.
    |
    */
    'paths' => [
        'base_url' => '', // Set this to config('app.url') in your application
        'prefix' => '',
        'suffix' => '',
        'use_template_suffix' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for page structures and data.
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'tags' => ['filament-page-manager'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    |
    | Configure the navigation settings for Filament resources.
    |
    */
    'navigation' => [
        'group' => 'Content',
        'pages' => [
            'label' => 'Pages',
            'plural_label' => 'Pages',
            'icon' => 'heroicon-o-document-text',
            'sort' => 1,
        ],
        'regions' => [
            'label' => 'Regions',
            'plural_label' => 'Regions',
            'icon' => 'heroicon-o-rectangle-group',
            'sort' => 2,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features.
    |
    */
    'features' => [
        'pages' => true,
        'regions' => true,
        'seo' => true,
        'preview' => true,
        'drafts' => true,
        'revisions' => false,
        'import_export' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Default validation rules for pages and regions.
    |
    */
    'validation' => [
        'page_name_max_length' => 255,
        'region_name_max_length' => 255,
        'slug_pattern' => '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
    ],
];
