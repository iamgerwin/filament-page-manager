<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void registerTemplate(string $templateClass)
 * @method static array getTemplates(string $type = null)
 * @method static array getPageTemplates()
 * @method static array getRegionTemplates()
 * @method static void setLocales(array $locales)
 * @method static array getLocales()
 * @method static array getPagesStructure(bool $withDrafts = false)
 * @method static \Illuminate\Database\Eloquent\Collection getPages(array $templates = [], bool $withDrafts = false)
 * @method static \IamGerwin\FilamentPageManager\Models\Page|null getPageBySlug(string $slug, ?string $locale = null)
 * @method static \IamGerwin\FilamentPageManager\Models\Page|null getPageByTemplate(string $template)
 * @method static \Illuminate\Database\Eloquent\Collection getRegions(array $templates = [])
 * @method static \IamGerwin\FilamentPageManager\Models\Region|null getRegionByName(string $name)
 * @method static array formatPage(\IamGerwin\FilamentPageManager\Models\Page $page, ?string $locale = null)
 * @method static array formatRegion(\IamGerwin\FilamentPageManager\Models\Region $region, ?string $locale = null)
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