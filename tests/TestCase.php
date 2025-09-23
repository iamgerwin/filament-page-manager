<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager\Tests;

use Filament\FilamentServiceProvider;
use IamGerwin\FilamentPageManager\FilamentPageManagerServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'IamGerwin\\FilamentPageManager\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            FilamentServiceProvider::class,
            FilamentPageManagerServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        config()->set('filament-page-manager.locales', [
            'en' => 'English',
            'es' => 'Spanish',
        ]);

        $migration = include __DIR__ . '/../database/migrations/create_filament_page_manager_pages_table.php';
        $migration->up();

        $migration = include __DIR__ . '/../database/migrations/create_filament_page_manager_regions_table.php';
        $migration->up();
    }
}
