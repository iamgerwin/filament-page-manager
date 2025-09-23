<?php

declare(strict_types=1);

use IamGerwin\FilamentPageManager\Facades\FilamentPageManager;
use IamGerwin\FilamentPageManager\Models\Page;
use IamGerwin\FilamentPageManager\Models\Region;
use IamGerwin\FilamentPageManager\Templates\AbstractPageTemplate;
use IamGerwin\FilamentPageManager\Templates\AbstractRegionTemplate;

class TestPageTemplate extends AbstractPageTemplate
{
    public function name(): string
    {
        return 'Test Page';
    }

    public function fields(): array
    {
        return [];
    }
}

class TestRegionTemplate extends AbstractRegionTemplate
{
    public function name(): string
    {
        return 'Test Region';
    }

    public function fields(): array
    {
        return [];
    }
}

beforeEach(function () {
    FilamentPageManager::registerTemplate(TestPageTemplate::class);
    FilamentPageManager::registerTemplate(TestRegionTemplate::class);
});

it('can register and retrieve templates', function () {
    $pageTemplates = FilamentPageManager::getPageTemplates();
    $regionTemplates = FilamentPageManager::getRegionTemplates();

    expect($pageTemplates)->toContain(TestPageTemplate::class)
        ->and($regionTemplates)->toContain(TestRegionTemplate::class);
});

it('can set and get locales', function () {
    FilamentPageManager::setLocales([
        'en' => 'English',
        'fr' => 'French',
        'de' => 'German',
    ]);

    $locales = FilamentPageManager::getLocales();

    expect($locales)->toHaveCount(3)
        ->and($locales)->toHaveKey('en')
        ->and($locales)->toHaveKey('fr')
        ->and($locales)->toHaveKey('de');
});

it('can get pages structure', function () {
    $parent = Page::create([
        'name' => 'Parent',
        'template' => TestPageTemplate::class,
        'active' => true,
    ]);

    Page::create([
        'name' => 'Child 1',
        'template' => TestPageTemplate::class,
        'parent_id' => $parent->id,
        'active' => true,
    ]);

    Page::create([
        'name' => 'Child 2',
        'template' => TestPageTemplate::class,
        'parent_id' => $parent->id,
        'active' => false,
    ]);

    $structure = FilamentPageManager::getPagesStructure();

    expect($structure)->toHaveCount(1)
        ->and($structure[0]['name'])->toBe('Parent')
        ->and($structure[0]['children'])->toHaveCount(1);

    $structureWithDrafts = FilamentPageManager::getPagesStructure(true);

    expect($structureWithDrafts[0]['children'])->toHaveCount(2);
});

it('can get pages by template', function () {
    Page::create([
        'name' => 'Page 1',
        'template' => TestPageTemplate::class,
        'active' => true,
    ]);

    Page::create([
        'name' => 'Page 2',
        'template' => TestPageTemplate::class,
        'active' => true,
    ]);

    Page::create([
        'name' => 'Page 3',
        'template' => 'OtherTemplate',
        'active' => true,
    ]);

    $pages = FilamentPageManager::getPages([TestPageTemplate::class]);

    expect($pages)->toHaveCount(2);
});

it('can get page by slug', function () {
    $page = Page::create([
        'name' => 'Test Page',
        'template' => TestPageTemplate::class,
        'slug' => ['en' => 'test-page'],
        'active' => true,
    ]);

    $foundPage = FilamentPageManager::getPageBySlug('test-page', 'en');

    expect($foundPage)->toBeInstanceOf(Page::class)
        ->and($foundPage->id)->toBe($page->id);

    $notFound = FilamentPageManager::getPageBySlug('non-existent', 'en');

    expect($notFound)->toBeNull();
});

it('can get page by template', function () {
    $page = Page::create([
        'name' => 'Unique Template Page',
        'template' => TestPageTemplate::class,
        'active' => true,
    ]);

    $foundPage = FilamentPageManager::getPageByTemplate(TestPageTemplate::class);

    expect($foundPage)->toBeInstanceOf(Page::class)
        ->and($foundPage->id)->toBe($page->id);
});

it('can get regions', function () {
    Region::create([
        'name' => 'Region 1',
        'template' => TestRegionTemplate::class,
    ]);

    Region::create([
        'name' => 'Region 2',
        'template' => TestRegionTemplate::class,
    ]);

    Region::create([
        'name' => 'Region 3',
        'template' => 'OtherTemplate',
    ]);

    $regions = FilamentPageManager::getRegions([TestRegionTemplate::class]);

    expect($regions)->toHaveCount(2);
});

it('can get region by name', function () {
    $region = Region::create([
        'name' => 'Header Region',
        'template' => TestRegionTemplate::class,
    ]);

    $foundRegion = FilamentPageManager::getRegionByName('Header Region');

    expect($foundRegion)->toBeInstanceOf(Region::class)
        ->and($foundRegion->id)->toBe($region->id);
});

it('can format page data', function () {
    $page = Page::create([
        'name' => 'Formatted Page',
        'template' => TestPageTemplate::class,
        'slug' => ['en' => 'formatted-page'],
        'active' => true,
        'data' => ['en' => ['content' => 'Test content']],
        'seo' => ['en' => ['title' => 'SEO Title']],
    ]);

    $formatted = FilamentPageManager::formatPage($page, 'en');

    expect($formatted)->toBeArray()
        ->and($formatted)->toHaveKeys(['id', 'name', 'slug', 'path', 'template', 'active', 'seo', 'data'])
        ->and($formatted['slug'])->toBe('formatted-page')
        ->and($formatted['seo'])->toMatchArray(['title' => 'SEO Title']);
});

it('can format region data', function () {
    $region = Region::create([
        'name' => 'Formatted Region',
        'template' => TestRegionTemplate::class,
        'data' => ['en' => ['content' => 'Region content']],
    ]);

    $formatted = FilamentPageManager::formatRegion($region, 'en');

    expect($formatted)->toBeArray()
        ->and($formatted)->toHaveKeys(['id', 'name', 'template', 'data'])
        ->and($formatted['name'])->toBe('Formatted Region');
});
