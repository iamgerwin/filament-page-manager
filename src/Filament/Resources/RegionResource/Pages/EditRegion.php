<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager\Filament\Resources\RegionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use IamGerwin\FilamentPageManager\Filament\Resources\RegionResource;

class EditRegion extends EditRecord
{
    protected static string $resource = RegionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}