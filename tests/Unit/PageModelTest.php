<?php

declare(strict_types=1);

use IamGerwin\FilamentPageManager\Models\Page;

it('can create a page', function () {
    $page = Page::create([
        'name' => 'Test Page',
        'template' => 'TestTemplate',
        'active' => true,
        'slug' => ['en' => 'test-page', 'es' => 'pagina-prueba'],
        'data' => ['title' => 'Test Title'],
    ]);

    expect($page)->toBeInstanceOf(Page::class)
        ->and($page->name)->toBe('Test Page')
        ->and($page->template)->toBe('TestTemplate')
        ->and($page->active)->toBeTrue();
});

it('can have parent-child relationships', function () {
    $parent = Page::create([
        'name' => 'Parent Page',
        'template' => 'TestTemplate',
    ]);

    $child = Page::create([
        'name' => 'Child Page',
        'template' => 'TestTemplate',
        'parent_id' => $parent->id,
    ]);

    expect($child->parent)->toBeInstanceOf(Page::class)
        ->and($child->parent->id)->toBe($parent->id)
        ->and($parent->children)->toHaveCount(1)
        ->and($parent->children->first()->id)->toBe($child->id);
});

it('can get ancestors', function () {
    $grandparent = Page::create([
        'name' => 'Grandparent',
        'template' => 'TestTemplate',
    ]);

    $parent = Page::create([
        'name' => 'Parent',
        'template' => 'TestTemplate',
        'parent_id' => $grandparent->id,
    ]);

    $child = Page::create([
        'name' => 'Child',
        'template' => 'TestTemplate',
        'parent_id' => $parent->id,
    ]);

    $ancestors = $child->ancestors();

    expect($ancestors)->toHaveCount(2)
        ->and($ancestors[0]->id)->toBe($grandparent->id)
        ->and($ancestors[1]->id)->toBe($parent->id);
});

it('can get descendants', function () {
    $parent = Page::create([
        'name' => 'Parent',
        'template' => 'TestTemplate',
    ]);

    $child1 = Page::create([
        'name' => 'Child 1',
        'template' => 'TestTemplate',
        'parent_id' => $parent->id,
    ]);

    $child2 = Page::create([
        'name' => 'Child 2',
        'template' => 'TestTemplate',
        'parent_id' => $parent->id,
    ]);

    $grandchild = Page::create([
        'name' => 'Grandchild',
        'template' => 'TestTemplate',
        'parent_id' => $child1->id,
    ]);

    $descendants = $parent->descendants();

    expect($descendants)->toHaveCount(3);
});

it('can generate paths correctly', function () {
    $parent = Page::create([
        'name' => 'Products',
        'template' => 'TestTemplate',
        'slug' => ['en' => 'products'],
    ]);

    $child = Page::create([
        'name' => 'Electronics',
        'template' => 'TestTemplate',
        'parent_id' => $parent->id,
        'slug' => ['en' => 'electronics'],
    ]);

    expect($child->getPath('en'))->toBe('/products/electronics');
});

it('can be published and unpublished', function () {
    $page = Page::create([
        'name' => 'Test Page',
        'template' => 'TestTemplate',
        'active' => false,
    ]);

    expect($page->isDraft())->toBeTrue()
        ->and($page->isPublished())->toBeFalse();

    $page->publish();

    expect($page->isDraft())->toBeFalse()
        ->and($page->isPublished())->toBeTrue();

    $page->unpublish();

    expect($page->isDraft())->toBeTrue()
        ->and($page->isPublished())->toBeFalse();
});

it('can be duplicated', function () {
    $page = Page::create([
        'name' => 'Original Page',
        'template' => 'TestTemplate',
        'slug' => ['en' => 'original-page'],
        'data' => ['content' => 'Original content'],
    ]);

    $duplicate = $page->duplicate();

    expect($duplicate)->toBeInstanceOf(Page::class)
        ->and($duplicate->id)->not->toBe($page->id)
        ->and($duplicate->name)->toBe('Original Page (Copy)')
        ->and($duplicate->template)->toBe($page->template)
        ->and($duplicate->active)->toBeFalse();
});

it('can handle translations', function () {
    $page = Page::create([
        'name' => 'Multilingual Page',
        'template' => 'TestTemplate',
        'slug' => [
            'en' => 'multilingual-page',
            'es' => 'pagina-multilingue',
        ],
        'data' => [
            'en' => ['title' => 'English Title'],
            'es' => ['title' => 'Título Español'],
        ],
    ]);

    expect($page->getTranslation('slug', 'en'))->toBe('multilingual-page')
        ->and($page->getTranslation('slug', 'es'))->toBe('pagina-multilingue')
        ->and($page->getTranslation('data', 'en'))->toMatchArray(['title' => 'English Title'])
        ->and($page->getTranslation('data', 'es'))->toMatchArray(['title' => 'Título Español']);
});
