<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager;

use IamGerwin\FilamentPageManager\Models\Page;
use IamGerwin\FilamentPageManager\Models\Region;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class FilamentPageManager
{
    protected array $templates = [];

    protected array $locales = [];

    public function registerTemplate(string $templateClass): void
    {
        $this->templates[] = $templateClass;
    }

    public function getTemplates(?string $type = null): array
    {
        if ($type === null) {
            return $this->templates;
        }

        return array_filter($this->templates, function ($template) use ($type) {
            return (new $template)->type() === $type;
        });
    }

    public function getPageTemplates(): array
    {
        return $this->getTemplates('page');
    }

    public function getRegionTemplates(): array
    {
        return $this->getTemplates('region');
    }

    public function setLocales(array $locales): void
    {
        $this->locales = $locales;
    }

    public function getLocales(): array
    {
        if (empty($this->locales)) {
            $this->locales = config('filament-page-manager.locales', []);
        }

        return $this->locales;
    }

    public function getPagesStructure(bool $withDrafts = false): array
    {
        $cacheKey = 'filament-page-manager.pages_structure'.($withDrafts ? '_with_drafts' : '');

        return Cache::remember($cacheKey, config('filament-page-manager.cache_ttl', 3600), function () use ($withDrafts) {
            $query = $this->getPageModel()::query()
                ->with(['children'])
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->orderBy('name');

            if (! $withDrafts) {
                $query->where('active', true);
            }

            return $this->buildTree($query->get());
        });
    }

    public function getPages(array $templates = [], bool $withDrafts = false): Collection
    {
        $query = $this->getPageModel()::query();

        if (! $withDrafts) {
            $query->where('active', true);
        }

        if (! empty($templates)) {
            $query->whereIn('template', $templates);
        }

        return $query->orderBy('sort_order')->orderBy('name')->get();
    }

    public function getPageBySlug(string $slug, ?string $locale = null): ?Page
    {
        $locale = $locale ?? app()->getLocale();

        return $this->getPageModel()::query()
            ->where('active', true)
            ->where("slug->{$locale}", $slug)
            ->first();
    }

    public function getPageByTemplate(string $template): ?Page
    {
        return $this->getPageModel()::query()
            ->where('active', true)
            ->where('template', $template)
            ->first();
    }

    public function getRegions(array $templates = []): Collection
    {
        $query = $this->getRegionModel()::query();

        if (! empty($templates)) {
            $query->whereIn('template', $templates);
        }

        return $query->orderBy('name')->get();
    }

    public function getRegionByName(string $name): ?Region
    {
        return $this->getRegionModel()::query()
            ->where('name', $name)
            ->first();
    }

    public function formatPage(Page $page, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $template = new ($page->template)();

        return [
            'id' => $page->id,
            'name' => $page->name,
            'slug' => $page->getTranslation('slug', $locale),
            'path' => $page->getPath($locale),
            'template' => $page->template,
            'parent_id' => $page->parent_id,
            'active' => $page->active,
            'seo' => $page->getTranslation('seo', $locale, []),
            'data' => $template->resolve($page, $locale),
            'created_at' => $page->created_at,
            'updated_at' => $page->updated_at,
        ];
    }

    public function formatRegion(Region $region, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $template = new ($region->template)();

        return [
            'id' => $region->id,
            'name' => $region->name,
            'template' => $region->template,
            'data' => $template->resolve($region, $locale),
            'created_at' => $region->created_at,
            'updated_at' => $region->updated_at,
        ];
    }

    public function clearCache(): void
    {
        Cache::forget('filament-page-manager.pages_structure');
        Cache::forget('filament-page-manager.pages_structure_with_drafts');
    }

    protected function buildTree(Collection $elements, $parentId = null): array
    {
        $tree = [];

        foreach ($elements as $element) {
            if ($element->parent_id == $parentId) {
                $children = $this->buildTree($elements, $element->id);
                if ($children) {
                    $element->children = $children;
                }
                $tree[] = $element;
            }
        }

        return $tree;
    }

    protected function getPageModel(): string
    {
        return config('filament-page-manager.models.page', Page::class);
    }

    protected function getRegionModel(): string
    {
        return config('filament-page-manager.models.region', Region::class);
    }
}
