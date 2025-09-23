<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use IamGerwin\FilamentPageManager\Facades\FilamentPageManager;
use IamGerwin\FilamentPageManager\Filament\Resources\PageResource\Pages as PagePages;
use IamGerwin\FilamentPageManager\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string $navigationIcon = 'heroicon-o-document-text';

    protected static string $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Pages';

    protected static ?int $navigationSort = 1;

    public static function getModel(): string
    {
        return config('filament-page-manager.models.page', Page::class);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(2)
                    ->schema([
                        Section::make('Basic Information')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Page Name')
                                    ->required()
                                    ->maxLength(config('filament-page-manager.validation.page_name_max_length', 255))
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                                Select::make('template')
                                    ->label('Template')
                                    ->options(static::getTemplateOptions())
                                    ->required()
                                    ->reactive()
                                    ->disabled(fn (?Model $record) => $record !== null)
                                    ->helperText(fn (?Model $record) => $record !== null ? 'Template cannot be changed after creation' : null),

                                Select::make('parent_id')
                                    ->label('Parent Page')
                                    ->relationship('parent', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                            ])
                            ->columnSpan(1),

                        Section::make('Status & Settings')
                            ->schema([
                                Toggle::make('active')
                                    ->label('Published')
                                    ->default(true)
                                    ->helperText('Unpublished pages are not visible on the frontend'),

                                TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Lower numbers appear first'),
                            ])
                            ->columnSpan(1),
                    ]),

                Section::make('Translations')
                    ->schema(function (?Model $record) {
                        $locales = FilamentPageManager::getLocales();
                        $tabs = [];

                        foreach ($locales as $locale => $label) {
                            $tabs[] = Tabs\Tab::make($label)
                                ->schema([
                                    TextInput::make("slug.{$locale}")
                                        ->label('Slug')
                                        ->maxLength(255)
                                        ->rules([
                                            'regex:'.config('filament-page-manager.validation.slug_pattern', '/^[a-z0-9]+(?:-[a-z0-9]+)*$/'),
                                        ])
                                        ->helperText('URL-friendly version of the page name'),
                                ]);
                        }

                        return [
                            Tabs::make('Locale Tabs')
                                ->tabs($tabs),
                        ];
                    }),

                Section::make('Template Content')
                    ->schema(fn (?Model $record, callable $get) => static::getTemplateFields($record, $get))
                    ->visible(fn (callable $get) => filled($get('template'))),

                Section::make('SEO')
                    ->schema(fn () => static::getSeoFields())
                    ->visible(fn () => config('filament-page-manager.seo.enabled', true))
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('template')
                    ->label('Template')
                    ->formatStateUsing(fn (string $state) => static::getTemplateName($state))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('active')
                    ->label('Published')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('template')
                    ->label('Template')
                    ->options(static::getTemplateOptions()),

                Tables\Filters\TernaryFilter::make('active')
                    ->label('Published'),

                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Parent')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Page $record) => $record->getUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (Page $record) => $record->active),

                Tables\Actions\EditAction::make(),

                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->requiresConfirmation()
                    ->action(fn (Page $record) => $record->duplicate()),

                Tables\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->publish())
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish')
                        ->icon('heroicon-o-x-mark')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->unpublish())
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => PagePages\ListPages::route('/'),
            'create' => PagePages\CreatePage::route('/create'),
            'edit' => PagePages\EditPage::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected static function getTemplateOptions(): array
    {
        $templates = FilamentPageManager::getPageTemplates();
        $options = [];

        foreach ($templates as $templateClass) {
            $template = new $templateClass;
            $options[$templateClass] = $template->name();
        }

        return $options;
    }

    protected static function getTemplateName(string $templateClass): string
    {
        if (! class_exists($templateClass)) {
            return 'Unknown';
        }

        $template = new $templateClass;

        return $template->name();
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    protected static function getTemplateFields(?Model $record, callable $get): array
    {
        $templateClass = $record?->template ?? $get('template');

        if (! $templateClass || ! class_exists($templateClass)) {
            return [];
        }

        $template = new $templateClass;
        $fields = $template->fields();
        $locales = FilamentPageManager::getLocales();

        if (count($locales) <= 1) {
            return static::prefixFieldNames($fields, 'data');
        }

        $tabs = [];
        foreach ($locales as $locale => $label) {
            $tabs[] = Tabs\Tab::make($label)
                ->schema(static::prefixFieldNames($fields, "data.{$locale}"));
        }

        return [
            Tabs::make('Content Translations')
                ->tabs($tabs),
        ];
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    protected static function getSeoFields(): array
    {
        $seoConfig = config('filament-page-manager.seo.fields', []);
        $locales = FilamentPageManager::getLocales();
        $fields = [];

        if (count($locales) <= 1) {
            foreach ($seoConfig as $key => $config) {
                $fields[] = static::createSeoField($key, $config, 'seo');
            }

            return $fields;
        }

        $tabs = [];
        foreach ($locales as $locale => $label) {
            $localeFields = [];
            foreach ($seoConfig as $key => $config) {
                $localeFields[] = static::createSeoField($key, $config, "seo.{$locale}");
            }
            $tabs[] = Tabs\Tab::make($label)->schema($localeFields);
        }

        return [
            Tabs::make('SEO Translations')->tabs($tabs),
        ];
    }

    /**
     * @param array<string, mixed> $config
     */
    protected static function createSeoField(string $key, array $config, string $prefix): Component
    {
        $name = "{$prefix}.{$key}";

        return match ($config['type'] ?? 'text') {
            'textarea' => Forms\Components\Textarea::make($name)
                ->label($config['label'] ?? ucfirst($key))
                ->maxLength($config['maxLength'] ?? null)
                ->required($config['required'] ?? false)
                ->helperText($config['helperText'] ?? null)
                ->rows(3),

            'image' => Forms\Components\FileUpload::make($name)
                ->label($config['label'] ?? ucfirst($key))
                ->image()
                ->required($config['required'] ?? false)
                ->helperText($config['helperText'] ?? null),

            default => Forms\Components\TextInput::make($name)
                ->label($config['label'] ?? ucfirst($key))
                ->maxLength($config['maxLength'] ?? null)
                ->required($config['required'] ?? false)
                ->helperText($config['helperText'] ?? null),
        };
    }

    /**
     * @param array<int, \Filament\Forms\Components\Component> $fields
     * @return array<int, \Filament\Forms\Components\Component>
     */
    protected static function prefixFieldNames(array $fields, string $prefix): array
    {
        return array_map(function ($field) use ($prefix) {
            if ($field instanceof Component && $field->getName()) {
                $originalName = $field->getName();
                if (! str_starts_with($originalName, $prefix.'.')) {
                    $field->name("{$prefix}.{$originalName}");
                }
            }

            return $field;
        }, $fields);
    }

    public static function getNavigationLabel(): string
    {
        return config('filament-page-manager.navigation.pages.plural_label', 'Pages');
    }

    public static function getNavigationIcon(): string
    {
        return config('filament-page-manager.navigation.pages.icon', 'heroicon-o-document-text');
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-page-manager.navigation.pages.sort', 1);
    }

    public static function getNavigationGroup(): ?string
    {
        return config('filament-page-manager.navigation.group', 'Content');
    }
}
