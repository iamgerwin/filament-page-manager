<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void registerTemplate(string $templateClass)
 * @method static array<int, class-string> getTemplates(string $type = null)
 * @method static array<int, class-string> getPageTemplates()
 * @method static array<int, class-string> getRegionTemplates()
 * @method static void setLocales(array<string, string> $locales)
 * @method static array<string, string> getLocales()
 * @method static array<int, mixed> getPagesStructure(bool $withDrafts = false)
 * @method static \Illuminate\Database\Eloquent\Collection<int, \IamGerwin\FilamentPageManager\Models\Page> getPages(array<int, string> $templates = [], bool $withDrafts = false)
 * @method static \IamGerwin\FilamentPageManager\Models\Page|null getPageBySlug(string $slug, ?string $locale = null)
 * @method static \IamGerwin\FilamentPageManager\Models\Page|null getPageByTemplate(string $template)
 * @method static \Illuminate\Database\Eloquent\Collection<int, \IamGerwin\FilamentPageManager\Models\Region> getRegions(array<int, string> $templates = [])
 * @method static \IamGerwin\FilamentPageManager\Models\Region|null getRegionByName(string $name)
 * @method static array<string, mixed> formatPage(\IamGerwin\FilamentPageManager\Models\Page $page, ?string $locale = null)
 * @method static array<string, mixed> formatRegion(\IamGerwin\FilamentPageManager\Models\Region $region, ?string $locale = null)
 * @method static void clearCache()
 *
 * @see \IamGerwin\FilamentPageManager\FilamentPageManager
 */
class FilamentPageManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'filament-page-manager';
    }
}
