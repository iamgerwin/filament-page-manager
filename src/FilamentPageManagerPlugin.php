<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentPageManagerPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-page-manager';
    }

    public function register(Panel $panel): void
    {
        if (config('filament-page-manager.features.pages', true)) {
            $panel->resources([
                config('filament-page-manager.resources.page'),
            ]);
        }

        if (config('filament-page-manager.features.regions', true)) {
            $panel->resources([
                config('filament-page-manager.resources.region'),
            ]);
        }
    }

    public function boot(Panel $panel): void
    {
        // Additional boot logic if needed
    }

    public static function make(): static
    {
        return new static;
    }
}
