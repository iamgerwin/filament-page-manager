<?php

declare(strict_types=1);

use IamGerwin\FilamentPageManager\Models\Region;

it('can create a region', function () {
    $region = Region::create([
        'name' => 'Hero Section',
        'template' => 'HeroTemplate',
        'data' => [
            'en' => ['title' => 'Welcome'],
            'es' => ['title' => 'Bienvenido'],
        ],
    ]);

    expect($region)->toBeInstanceOf(Region::class)
        ->and($region->name)->toBe('Hero Section')
        ->and($region->template)->toBe('HeroTemplate');
});

it('enforces unique region names', function () {
    Region::create([
        'name' => 'Unique Region',
        'template' => 'TestTemplate',
    ]);

    expect(fn () => Region::create([
        'name' => 'Unique Region',
        'template' => 'TestTemplate',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

it('can be duplicated with unique name', function () {
    $region = Region::create([
        'name' => 'Original Region',
        'template' => 'TestTemplate',
        'data' => ['content' => 'Original content'],
    ]);

    $duplicate = $region->duplicate();

    expect($duplicate)->toBeInstanceOf(Region::class)
        ->and($duplicate->id)->not->toBe($region->id)
        ->and($duplicate->name)->toBe('Original Region (Copy)')
        ->and($duplicate->template)->toBe($region->template);
});

it('can handle translations', function () {
    $region = Region::create([
        'name' => 'Footer',
        'template' => 'FooterTemplate',
        'data' => [
            'en' => [
                'copyright' => '© 2024 Company',
                'links' => ['Privacy', 'Terms'],
            ],
            'es' => [
                'copyright' => '© 2024 Empresa',
                'links' => ['Privacidad', 'Términos'],
            ],
        ],
    ]);

    expect($region->getTranslation('data', 'en')['copyright'])->toBe('© 2024 Company')
        ->and($region->getTranslation('data', 'es')['copyright'])->toBe('© 2024 Empresa');
});