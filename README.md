# Filament Page Manager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/iamgerwin/filament-page-manager.svg?style=flat-square)](https://packagist.org/packages/iamgerwin/filament-page-manager)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/iamgerwin/filament-page-manager/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/iamgerwin/filament-page-manager/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/iamgerwin/filament-page-manager/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/iamgerwin/filament-page-manager/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/iamgerwin/filament-page-manager.svg?style=flat-square)](https://packagist.org/packages/iamgerwin/filament-page-manager)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![PHP Version](https://img.shields.io/packagist/php-v/iamgerwin/filament-page-manager.svg?style=flat-square)](https://packagist.org/packages/iamgerwin/filament-page-manager)

A comprehensive page management system for Filament v4 with advanced features including template-based content management, regions, multilingual support, SEO optimization, and hierarchical page structures.

## Features

- **Page Management**: Create and manage static pages with hierarchical structure
- **Template System**: Flexible template-based content architecture
- **Region Management**: Reusable content blocks across pages
- **Multilingual Support**: Full translation support with locale management
- **SEO Optimization**: Built-in SEO fields and meta tag management
- **Drag & Drop Sorting**: Reorderable pages with sort order
- **Draft/Publish System**: Control page visibility with publish states
- **Page Duplication**: Quick page copying with automatic slug generation
- **Cache Management**: Optimized performance with intelligent caching
- **PHP 8.3 Features**: Leveraging modern PHP capabilities

## Requirements

- PHP 8.3 or higher
- Laravel 11.0 or higher
- Filament 4.0 or higher

## Installation

You can install the package via composer:

```bash
composer require iamgerwin/filament-page-manager
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-page-manager-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-page-manager-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-page-manager-views"
```

## Configuration

The configuration file `config/filament-page-manager.php` allows you to customize:

- Database table names
- Model and resource classes
- Template registration
- Locale settings
- SEO configuration
- Navigation settings
- Feature toggles
- Cache settings

### Basic Configuration

```php
return [
    'tables' => [
        'pages' => 'fpm_pages',
        'regions' => 'fpm_regions',
    ],

    'locales' => [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
    ],

    'default_locale' => 'en',

    'seo' => [
        'enabled' => true,
        'fields' => [
            'title' => ['label' => 'SEO Title', 'maxLength' => 60],
            'description' => ['label' => 'SEO Description', 'maxLength' => 160],
        ],
    ],
];
```

## Usage

### Creating Page Templates

Create a new page template:

```bash
php artisan filament-page-manager:make-template HomePage --type=page
```

This generates a template class in `app/PageTemplates/HomePageTemplate.php`:

```php
<?php

namespace App\PageTemplates;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use IamGerwin\FilamentPageManager\Templates\AbstractPageTemplate;

class HomePageTemplate extends AbstractPageTemplate
{
    public function name(): string
    {
        return 'Home Page';
    }

    public function fields(): array
    {
        return [
            Section::make('Hero Section')
                ->schema([
                    TextInput::make('hero_title')
                        ->label('Hero Title')
                        ->required()
                        ->maxLength(255),

                    RichEditor::make('hero_content')
                        ->label('Hero Content')
                        ->required(),
                ]),
        ];
    }

    public function pathSuffix(): ?string
    {
        return null; // or '.html' for specific URL patterns
    }
}
```

### Creating Region Templates

Create a region template:

```bash
php artisan filament-page-manager:make-template Footer --type=region
```

```php
<?php

namespace App\RegionTemplates;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use IamGerwin\FilamentPageManager\Templates\AbstractRegionTemplate;

class FooterTemplate extends AbstractRegionTemplate
{
    public function name(): string
    {
        return 'Footer';
    }

    public function fields(): array
    {
        return [
            TextInput::make('copyright')
                ->label('Copyright Text')
                ->required(),

            Repeater::make('links')
                ->label('Footer Links')
                ->schema([
                    TextInput::make('title')->required(),
                    TextInput::make('url')->url()->required(),
                ])
                ->columns(2),
        ];
    }
}
```

### Registering Templates

Register your templates in the configuration file:

```php
'templates' => [
    App\PageTemplates\HomePageTemplate::class,
    App\PageTemplates\AboutPageTemplate::class,
    App\PageTemplates\ContactPageTemplate::class,
    App\RegionTemplates\HeaderTemplate::class,
    App\RegionTemplates\FooterTemplate::class,
],
```

### Using in Your Application

#### Retrieving Pages

```php
use IamGerwin\FilamentPageManager\Facades\FilamentPageManager;

// Get all published pages
$pages = FilamentPageManager::getPages();

// Get pages by template
$blogPages = FilamentPageManager::getPages([BlogPageTemplate::class]);

// Get page by slug
$page = FilamentPageManager::getPageBySlug('about-us', 'en');

// Get hierarchical page structure
$structure = FilamentPageManager::getPagesStructure();

// Get formatted page data for frontend
$pageData = FilamentPageManager::formatPage($page, 'en');
```

#### Using Helper Functions

```php
// Get page by slug
$page = fpm_get_page_by_slug('about-us');

// Get pages structure
$structure = fpm_get_pages_structure();

// Get region by name
$footer = fpm_get_region('footer');

// Format data for frontend
$formatted = fpm_format_page($page);
```

#### Working with Regions

```php
// Get all regions
$regions = FilamentPageManager::getRegions();

// Get region by name
$header = FilamentPageManager::getRegionByName('header');

// Format region data
$headerData = FilamentPageManager::formatRegion($header, 'en');
```

### Blade Views Integration

```blade
@php
    $page = fpm_get_page_by_slug(request()->path());
    $header = fpm_get_region('header');
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <title>{{ $page->seo['title'] ?? $page->name }}</title>
    <meta name="description" content="{{ $page->seo['description'] ?? '' }}">
</head>
<body>
    @if($header)
        <header>
            {!! $header->data['content'] !!}
        </header>
    @endif

    <main>
        <h1>{{ $page->data['title'] }}</h1>
        {!! $page->data['content'] !!}
    </main>
</body>
</html>
```

## Advanced Features

### Multilingual Support

Configure multiple locales in your config:

```php
'locales' => [
    'en' => 'English',
    'es' => 'Spanish',
    'fr' => 'French',
    'de' => 'German',
],
```

Access translated content:

```php
$page->getTranslation('slug', 'es');
$page->setTranslation('data', 'fr', ['title' => 'Titre Français']);
```

### Custom Models

Extend the base models for custom functionality:

```php
namespace App\Models;

use IamGerwin\FilamentPageManager\Models\Page as BasePage;

class Page extends BasePage
{
    public function generateMetaTags(): string
    {
        // Custom meta tag generation
    }
}
```

Update configuration:

```php
'models' => [
    'page' => App\Models\Page::class,
],
```

### Cache Management

The package includes intelligent caching:

```php
// Clear all caches
FilamentPageManager::clearCache();

// Or using helper
fpm_clear_cache();
```

Configure cache settings:

```php
'cache' => [
    'enabled' => true,
    'ttl' => 3600, // 1 hour
    'tags' => ['filament-page-manager'],
],
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover any security-related issues, please email iamgerwin@live.com instead of using the issue tracker.

## Credits

- [iamgerwin](https://github.com/iamgerwin)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
