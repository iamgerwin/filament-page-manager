<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager\Templates;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use IamGerwin\FilamentPageManager\Contracts\TemplateContract;
use IamGerwin\FilamentPageManager\Facades\FilamentPageManager;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractTemplate implements TemplateContract
{
    /**
     * Get the template name.
     */
    abstract public function name(): string;

    /**
     * Get the form fields for this template.
     *
     * @return array<int, mixed>
     */
    abstract public function fields(): array;

    /**
     * Get the template type (page or region).
     */
    public function type(): string
    {
        return 'page';
    }

    /**
     * Resolve the data for frontend consumption.
     *
     * @return array<string, mixed>
     */
    public function resolve(Model $model, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();

        // Check if model has getTranslation method (HasTranslations trait)
        if (method_exists($model, 'getTranslation')) {
            $data = $model->getTranslation('data', $locale, []);
        } else {
            // Fallback to direct attribute access
            $data = $model->getAttribute('data') ?? [];
            if (is_array($data) && isset($data[$locale])) {
                $data = $data[$locale];
            }
        }

        return $this->processData($data, $locale);
    }

    /**
     * Process the raw data before returning.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function processData(array $data, string $locale): array
    {
        return $data;
    }

    /**
     * Get the path suffix for this template (pages only).
     */
    public function pathSuffix(): ?string
    {
        return null;
    }

    /**
     * Whether this template can only have a single instance.
     */
    public function unique(): bool
    {
        return false;
    }

    /**
     * Get the template description.
     */
    public function description(): ?string
    {
        return null;
    }

    /**
     * Get the template icon.
     */
    public function icon(): ?string
    {
        return 'heroicon-o-document';
    }

    /**
     * Get SEO fields if enabled.
     *
     * @return array<int, mixed>
     */
    public function seoFields(): array
    {
        if (! config('filament-page-manager.seo.enabled', true)) {
            return [];
        }

        $fields = [];
        $seoConfig = config('filament-page-manager.seo.fields', []);

        foreach ($seoConfig as $key => $config) {
            $field = TextInput::make("seo.{$key}")
                ->label($config['label'] ?? ucfirst($key))
                ->maxLength($config['maxLength'] ?? null)
                ->required($config['required'] ?? false)
                ->helperText($config['helperText'] ?? null);

            if ($config['type'] === 'textarea') {
                $field = \Filament\Forms\Components\Textarea::make("seo.{$key}")
                    ->label($config['label'] ?? ucfirst($key))
                    ->maxLength($config['maxLength'] ?? null)
                    ->required($config['required'] ?? false)
                    ->helperText($config['helperText'] ?? null)
                    ->rows(3);
            } elseif ($config['type'] === 'image') {
                $field = \Filament\Forms\Components\FileUpload::make("seo.{$key}")
                    ->label($config['label'] ?? ucfirst($key))
                    ->image()
                    ->required($config['required'] ?? false)
                    ->helperText($config['helperText'] ?? null);
            }

            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * Wrap fields in a translatable container if needed.
     *
     * @param  array<int, mixed>  $fields
     * @return array<int, mixed>
     */
    protected function makeTranslatable(array $fields): array
    {
        $locales = FilamentPageManager::getLocales();

        if (count($locales) <= 1) {
            return $fields;
        }

        $tabs = [];
        foreach ($locales as $locale => $label) {
            $tabs[] = Tab::make($label)
                ->schema($this->cloneFieldsForLocale($fields, $locale));
        }

        return [
            Tabs::make('Translations')
                ->tabs($tabs),
        ];
    }

    /**
     * Clone fields for a specific locale.
     *
     * @param  array<int, mixed>  $fields
     * @return array<int, mixed>
     */
    protected function cloneFieldsForLocale(array $fields, string $locale): array
    {
        $cloned = [];

        foreach ($fields as $field) {
            if ($field instanceof Forms\Components\Component) {
                $clonedField = clone $field;
                $name = $clonedField->getName();
                if ($name && ! str_starts_with($name, 'data.')) {
                    $clonedField->name("data.{$locale}.{$name}");
                }
                $cloned[] = $clonedField;
            }
        }

        return $cloned;
    }

    /**
     * Helper method to create a section with fields.
     *
     * @param  array<int, mixed>  $fields
     */
    protected function section(string $title, array $fields, ?string $description = null): Section
    {
        $section = Section::make($title)
            ->schema($fields);

        if ($description) {
            $section->description($description);
        }

        return $section;
    }

    /**
     * Helper method to create a group with fields.
     *
     * @param  array<int, mixed>  $fields
     */
    protected function group(array $fields): Group
    {
        return Group::make($fields);
    }
}
