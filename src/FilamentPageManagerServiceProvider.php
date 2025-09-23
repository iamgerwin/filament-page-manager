<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use IamGerwin\FilamentPageManager\Console\Commands\CreateTemplateCommand;
use IamGerwin\FilamentPageManager\Facades\FilamentPageManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentPageManagerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-page-manager')
            ->hasConfigFile()
            ->hasMigrations([
                'create_filament_page_manager_pages_table',
                'create_filament_page_manager_regions_table',
            ])
            ->hasTranslations()
            ->hasCommands([
                CreateTemplateCommand::class,
            ])
            ->hasViews();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('filament-page-manager', function () {
            return new \IamGerwin\FilamentPageManager\FilamentPageManager;
        });

        // Load helper functions
        if (file_exists(__DIR__.'/Helpers/helpers.php')) {
            require_once __DIR__.'/Helpers/helpers.php';
        }
    }

    public function packageBooted(): void
    {
        // Register templates from config
        $templates = config('filament-page-manager.templates', []);
        foreach ($templates as $template) {
            if (class_exists($template)) {
                FilamentPageManager::registerTemplate($template);
            }
        }

        FilamentAsset::register([
            Css::make('filament-page-manager', __DIR__.'/../resources/css/filament-page-manager.css'),
        ], 'iamgerwin/filament-page-manager');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/filament-page-manager'),
        ], 'filament-page-manager-views');

        $this->publishes([
            __DIR__.'/../stubs' => base_path('stubs/filament-page-manager'),
        ], 'filament-page-manager-stubs');
    }
}
