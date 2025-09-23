<?php

declare(strict_types=1);

use IamGerwin\FilamentPageManager\Facades\FilamentPageManager;
use IamGerwin\FilamentPageManager\Models\Page;
use IamGerwin\FilamentPageManager\Models\Region;
use Illuminate\Database\Eloquent\Collection;

if (! function_exists('fpm')) {
    /**
     * Get the FilamentPageManager instance.
     */
    function fpm(): IamGerwin\FilamentPageManager\FilamentPageManager
    {
        return app('filament-page-manager');
    }
}

if (! function_exists('fpm_get_pages')) {
    /**
     * Get pages by templates.
     */
    function fpm_get_pages(array $templates = [], bool $withDrafts = false): Collection
    {
        return FilamentPageManager::getPages($templates, $withDrafts);
    }
}

if (! function_exists('fpm_get_page_by_slug')) {
    /**
     * Get a page by its slug.
     */
    function fpm_get_page_by_slug(string $slug, ?string $locale = null): ?Page
    {
        return FilamentPageManager::getPageBySlug($slug, $locale);
    }
}

if (! function_exists('fpm_get_page_by_template')) {
    /**
     * Get a page by its template.
     */
    function fpm_get_page_by_template(string $template): ?Page
    {
        return FilamentPageManager::getPageByTemplate($template);
    }
}

if (! function_exists('fpm_get_pages_structure')) {
    /**
     * Get the hierarchical structure of pages.
     */
    function fpm_get_pages_structure(bool $withDrafts = false): array
    {
        return FilamentPageManager::getPagesStructure($withDrafts);
    }
}

if (! function_exists('fpm_get_regions')) {
    /**
     * Get regions by templates.
     */
    function fpm_get_regions(array $templates = []): Collection
    {
        return FilamentPageManager::getRegions($templates);
    }
}

if (! function_exists('fpm_get_region')) {
    /**
     * Get a region by its name.
     */
    function fpm_get_region(string $name): ?Region
    {
        return FilamentPageManager::getRegionByName($name);
    }
}

if (! function_exists('fpm_format_page')) {
    /**
     * Format a page for frontend consumption.
     */
    function fpm_format_page(Page $page, ?string $locale = null): array
    {
        return FilamentPageManager::formatPage($page, $locale);
    }
}

if (! function_exists('fpm_format_region')) {
    /**
     * Format a region for frontend consumption.
     */
    function fpm_format_region(Region $region, ?string $locale = null): array
    {
        return FilamentPageManager::formatRegion($region, $locale);
    }
}

if (! function_exists('fpm_get_locales')) {
    /**
     * Get configured locales.
     */
    function fpm_get_locales(): array
    {
        return FilamentPageManager::getLocales();
    }
}

if (! function_exists('fpm_clear_cache')) {
    /**
     * Clear the page manager cache.
     */
    function fpm_clear_cache(): void
    {
        FilamentPageManager::clearCache();
    }
}
