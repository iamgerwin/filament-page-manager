<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager\Filament\Resources\RegionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use IamGerwin\FilamentPageManager\Filament\Resources\RegionResource;

class ListRegions extends ListRecords
{
    protected static string $resource = RegionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
