<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager\Templates;

abstract class AbstractRegionTemplate extends AbstractTemplate
{
    /**
     * Get the template type.
     */
    public function type(): string
    {
        return 'region';
    }

    /**
     * Get the template icon.
     */
    public function icon(): ?string
    {
        return 'heroicon-o-rectangle-group';
    }
}