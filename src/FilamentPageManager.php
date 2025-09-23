<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager;

use IamGerwin\FilamentPageManager\Models\Page;
use IamGerwin\FilamentPageManager\Models\Region;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class FilamentPageManager
{
    /**
     * @var array<int, string>
     */
    protected array $templates = [];

    /**
     * @var array<string, string>
     */
    protected array $locales = [];

    public function registerTemplate(string $templateClass): void
    {
        $this->templates[] = $templateClass;
    }

    /**
     * @return array<int, string>
     */
    public function getTemplates(?string $type = null): array
    {
        if ($type === null) {
            return $this->templates;
        }

        return array_filter($this->templates, function ($template) use ($type) {
            return (new $template)->type() === $type;
        });
    }

    /**
     * @return array<int, string>
     */
    public function getPageTemplates(): array
    {
        return $this->getTemplates('page');
    }

    /**
     * @return array<int, string>
     */
    public function getRegionTemplates(): array
    {
        return $this->getTemplates('region');
    }

    /**
     * @param  array<string, string>  $locales
     */
    public function setLocales(array $locales): void
    {
        $this->locales = $locales;
    }

    /**
     * @return array<string, string>
     */
    public function getLocales(): array
    {
        if (empty($this->locales)) {
            $this->locales = config('filament-page-manager.locales', []);
        }

        return $this->locales;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPagesStructure(bool $withDrafts = false): array
    {
        $cacheKey = 'filament-page-manager.pages_structure'.($withDrafts ? '_with_drafts' : '');

        return Cache::remember($cacheKey, config('filament-page-manager.cache_ttl', 3600), function () use ($withDrafts) {
            $query = $this->getPageModel()::query()
                ->orderBy('sort_order')
                ->orderBy('name');

            if (! $withDrafts) {
                $query->where('active', true);
            }

            $pages = $query->get();

            return $this->buildTree($pages, null, $withDrafts);
        });
    }

    /**
     * @param  array<int, string>  $templates
     * @return Collection<int, Page>
     */
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

    /**
     * @param  array<int, string>  $templates
     * @return Collection<int, Region>
     */
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

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @param  Collection<int, Page>  $elements
     * @param  int|null  $parentId
     * @return array<int, array<string, mixed>>
     */
    protected function buildTree(Collection $elements, $parentId = null, bool $withDrafts = false): array
    {
        $tree = [];

        foreach ($elements as $element) {
            if ($element->parent_id == $parentId) {
                // Filter children based on draft status
                $filteredChildren = $elements->filter(function ($child) use ($element, $withDrafts) {
                    return $child->parent_id == $element->id && ($withDrafts || $child->active);
                });

                $children = $this->buildTree($elements, $element->id, $withDrafts);

                $elementArray = [
                    'id' => $element->id,
                    'name' => $element->name,
                    'slug' => $element->slug,
                    'template' => $element->template,
                    'parent_id' => $element->parent_id,
                    'active' => $element->active,
                    'sort_order' => $element->sort_order,
                    'children' => $children,
                ];

                $tree[] = $elementArray;
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
