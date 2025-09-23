<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager\Filament\Resources\PageResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use IamGerwin\FilamentPageManager\Filament\Resources\PageResource;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! isset($data['sort_order'])) {
            $data['sort_order'] = 0;
        }

        return $data;
    }
}