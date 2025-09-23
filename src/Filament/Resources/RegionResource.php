<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager\Filament\Resources;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use IamGerwin\FilamentPageManager\Facades\FilamentPageManager;
use IamGerwin\FilamentPageManager\Filament\Resources\RegionResource\Pages as RegionPages;
use IamGerwin\FilamentPageManager\Models\Region;
use Illuminate\Database\Eloquent\Model;

class RegionResource extends Resource
{
    protected static ?string $model = Region::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Regions';

    protected static ?int $navigationSort = 2;

    public static function getModel(): string
    {
        return config('filament-page-manager.models.region', Region::class);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Region Name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(config('filament-page-manager.validation.region_name_max_length', 255))
                            ->helperText('Unique identifier for this region'),

                        Select::make('template')
                            ->label('Template')
                            ->options(static::getTemplateOptions())
                            ->required()
                            ->reactive()
                            ->disabled(fn (?Model $record) => $record !== null)
                            ->helperText(fn (?Model $record) => $record !== null ? 'Template cannot be changed after creation' : null),
                    ]),

                Section::make('Template Content')
                    ->schema(fn (?Model $record, callable $get) => static::getTemplateFields($record, $get))
                    ->visible(fn (callable $get) => filled($get('template'))),
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
            ])
            ->recordActions([
                Tables\Actions\EditAction::make(),

                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->requiresConfirmation()
                    ->action(fn (Region $record) => $record->duplicate()),

                Tables\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => RegionPages\ListRegions::route('/'),
            'create' => RegionPages\CreateRegion::route('/create'),
            'edit' => RegionPages\EditRegion::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected static function getTemplateOptions(): array
    {
        $templates = FilamentPageManager::getRegionTemplates();
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
        return config('filament-page-manager.navigation.regions.plural_label', 'Regions');
    }

    public static function getNavigationIcon(): string
    {
        return config('filament-page-manager.navigation.regions.icon', 'heroicon-o-rectangle-group');
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-page-manager.navigation.regions.sort', 2);
    }

    public static function getNavigationGroup(): ?string
    {
        return config('filament-page-manager.navigation.group', 'Content');
    }
}
