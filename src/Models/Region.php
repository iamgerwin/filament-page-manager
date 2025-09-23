<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager\Models;

use IamGerwin\FilamentPageManager\Facades\FilamentPageManager;
use IamGerwin\FilamentPageManager\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = ['id'];

    protected $casts = [
        'data' => 'json',
    ];

    protected $translatable = [
        'data',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            FilamentPageManager::clearCache();
        });

        static::deleted(function () {
            FilamentPageManager::clearCache();
        });
    }

    public function getTable(): string
    {
        return config('filament-page-manager.tables.regions', 'fpm_regions');
    }

    public function getTemplateInstance(): ?object
    {
        if (! $this->template) {
            return null;
        }

        return new ($this->template)();
    }

    public function duplicate(): static
    {
        $clone = $this->replicate();
        $clone->name = $this->generateUniqueName($this->name . ' (Copy)');
        $clone->save();

        return $clone;
    }

    protected function generateUniqueName(string $baseName): string
    {
        $name = $baseName;
        $counter = 1;

        while (static::where('name', $name)->exists()) {
            $name = "{$baseName} {$counter}";
            $counter++;
        }

        return $name;
    }
}