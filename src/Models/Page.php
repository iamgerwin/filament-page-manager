<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager\Models;

use IamGerwin\FilamentPageManager\Facades\FilamentPageManager;
use IamGerwin\FilamentPageManager\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property bool $active
 * @property int|null $parent_id
 * @property string $template
 * @property string $name
 * @property array<string, string>|null $slug
 * @property array<string, mixed>|null $seo
 * @property array<string, mixed>|null $data
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \IamGerwin\FilamentPageManager\Models\Page|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \IamGerwin\FilamentPageManager\Models\Page> $children
 *
 * @phpstan-use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>>
 */
class Page extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory;

    use HasTranslations;

    protected $guarded = ['id'];

    protected $casts = [
        'active' => 'boolean',
        'slug' => 'json',
        'seo' => 'json',
        'data' => 'json',
        'sort_order' => 'integer',
    ];

    /** @var array<int, string> */
    protected $translatable = [
        'slug',
        'seo',
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
        return config('filament-page-manager.tables.pages', 'fpm_pages');
    }

    /**
     * @return BelongsTo<self, self>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<self, self>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    /**
     * @return array<int, \IamGerwin\FilamentPageManager\Models\Page>
     */
    public function ancestors(): array
    {
        $ancestors = [];
        $parent = $this->parent;

        while ($parent) {
            $ancestors[] = $parent;
            $parent = $parent->parent;
        }

        return array_reverse($ancestors);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \IamGerwin\FilamentPageManager\Models\Page>
     */
    public function descendants(): \Illuminate\Support\Collection
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
        }

        return $descendants;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \IamGerwin\FilamentPageManager\Models\Page>
     */
    public function siblings(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('parent_id', $this->parent_id)
            ->where('id', '!=', $this->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function getPath(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $segments = [];

        foreach ($this->ancestors() as $ancestor) {
            $segments[] = $ancestor->getTranslation('slug', $locale);
        }

        $segments[] = $this->getTranslation('slug', $locale);

        $path = implode('/', array_filter($segments));

        if ($templateClass = $this->template) {
            $template = new $templateClass;
            if ($suffix = $template->pathSuffix()) {
                $path .= $suffix;
            }
        }

        $prefix = config('filament-page-manager.paths.prefix', '');
        $suffix = config('filament-page-manager.paths.suffix', '');

        if ($prefix) {
            $path = $prefix.'/'.$path;
        }

        if ($suffix && ! str_ends_with($path, $suffix)) {
            $path .= $suffix;
        }

        return '/'.ltrim($path, '/');
    }

    public function getUrl(?string $locale = null): string
    {
        $baseUrl = config('filament-page-manager.paths.base_url', '');

        return rtrim($baseUrl, '/').$this->getPath($locale);
    }

    public function getTemplateInstance(): ?object
    {
        if (! $this->template) {
            return null;
        }

        return new ($this->template)();
    }

    /**
     * @return array<int, array{name: string, url: string}>
     */
    public function getBreadcrumbs(?string $locale = null): array
    {
        $breadcrumbs = [];
        $ancestors = $this->ancestors();

        foreach ($ancestors as $ancestor) {
            $breadcrumbs[] = [
                'name' => $ancestor->name,
                'url' => $ancestor->getUrl($locale),
            ];
        }

        $breadcrumbs[] = [
            'name' => $this->name,
            'url' => $this->getUrl($locale),
        ];

        return $breadcrumbs;
    }

    public function isDraft(): bool
    {
        return ! $this->active;
    }

    public function isPublished(): bool
    {
        return $this->active;
    }

    public function publish(): bool
    {
        $this->active = true;

        return $this->save();
    }

    public function unpublish(): bool
    {
        $this->active = false;

        return $this->save();
    }

    public function duplicate(bool $includeChildren = false): static
    {
        $clone = $this->replicate();
        $clone->name = $this->name.' (Copy)';
        $slug = is_array($this->slug) ? $this->slug : [];
        $clone->slug = $this->generateUniqueSlug($slug);
        $clone->active = false;
        $clone->save();

        if ($includeChildren) {
            foreach ($this->children as $child) {
                $childClone = $child->duplicate($includeChildren);
                $childClone->parent_id = $clone->id;
                $childClone->save();
            }
        }

        return $clone;
    }

    /**
     * @param  array<string, string>  $slug
     * @return array<string, string>
     */
    protected function generateUniqueSlug(array $slug): array
    {
        $newSlug = [];

        // If slug is empty, generate from name
        if (empty($slug)) {
            $locales = config('filament-page-manager.locales', ['en' => 'English']);
            foreach (array_keys($locales) as $locale) {
                if (!is_string($locale)) {
                    continue;
                }
                $baseSlug = Str::slug($this->name.'-copy');
                $counter = 1;
                $uniqueSlug = $baseSlug;

                while (static::where("slug->{$locale}", $uniqueSlug)->exists()) {
                    $uniqueSlug = "{$baseSlug}-{$counter}";
                    $counter++;
                }

                $newSlug[$locale] = $uniqueSlug;
            }

            return $newSlug;
        }

        foreach ($slug as $locale => $value) {
            $baseSlug = Str::slug($value);
            $counter = 1;
            $uniqueSlug = $baseSlug;

            while (static::where("slug->{$locale}", $uniqueSlug)->exists()) {
                $uniqueSlug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $newSlug[$locale] = $uniqueSlug;
        }

        return $newSlug;
    }
}
