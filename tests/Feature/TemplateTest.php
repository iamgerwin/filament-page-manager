<?php

declare(strict_types=1);

use Filament\Forms\Components\TextInput;
use IamGerwin\FilamentPageManager\Models\Page;
use IamGerwin\FilamentPageManager\Templates\AbstractPageTemplate;

class SamplePageTemplate extends AbstractPageTemplate
{
    public function name(): string
    {
        return 'Sample Page';
    }

    public function fields(): array
    {
        return [
            TextInput::make('title')
                ->label('Title')
                ->required(),
            TextInput::make('subtitle')
                ->label('Subtitle'),
        ];
    }

    public function pathSuffix(): ?string
    {
        return '.html';
    }

    public function unique(): bool
    {
        return true;
    }

    public function description(): ?string
    {
        return 'A sample page template for testing';
    }

    public function icon(): ?string
    {
        return 'heroicon-o-document';
    }
}

it('can create a template with all properties', function () {
    $template = new SamplePageTemplate;

    expect($template->name())->toBe('Sample Page')
        ->and($template->type())->toBe('page')
        ->and($template->pathSuffix())->toBe('.html')
        ->and($template->unique())->toBeTrue()
        ->and($template->description())->toBe('A sample page template for testing')
        ->and($template->icon())->toBe('heroicon-o-document');
});

it('can get template fields', function () {
    $template = new SamplePageTemplate;
    $fields = $template->fields();

    expect($fields)->toBeArray()
        ->and($fields)->toHaveCount(2)
        ->and($fields[0])->toBeInstanceOf(TextInput::class);
});

it('can resolve template data', function () {
    $page = Page::create([
        'name' => 'Test Page',
        'template' => SamplePageTemplate::class,
        'data' => [
            'en' => [
                'title' => 'English Title',
                'subtitle' => 'English Subtitle',
            ],
            'es' => [
                'title' => 'Spanish Title',
                'subtitle' => 'Spanish Subtitle',
            ],
        ],
    ]);

    $template = new SamplePageTemplate;
    $resolvedEn = $template->resolve($page, 'en');
    $resolvedEs = $template->resolve($page, 'es');

    expect($resolvedEn)->toMatchArray([
        'title' => 'English Title',
        'subtitle' => 'English Subtitle',
    ])
        ->and($resolvedEs)->toMatchArray([
            'title' => 'Spanish Title',
            'subtitle' => 'Spanish Subtitle',
        ]);
});

it('can generate SEO fields when enabled', function () {
    config(['filament-page-manager.seo.enabled' => true]);
    config(['filament-page-manager.seo.fields' => [
        'title' => [
            'label' => 'SEO Title',
            'type' => 'text',
            'maxLength' => 60,
        ],
        'description' => [
            'label' => 'SEO Description',
            'type' => 'textarea',
            'maxLength' => 160,
        ],
    ]]);

    $template = new SamplePageTemplate;
    $seoFields = $template->seoFields();

    expect($seoFields)->toBeArray()
        ->and($seoFields)->toHaveCount(2);
});

it('returns empty SEO fields when disabled', function () {
    config(['filament-page-manager.seo.enabled' => false]);

    $template = new SamplePageTemplate;
    $seoFields = $template->seoFields();

    expect($seoFields)->toBeEmpty();
});
