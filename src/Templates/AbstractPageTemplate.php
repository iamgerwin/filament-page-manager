<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager\Templates;

abstract class AbstractPageTemplate extends AbstractTemplate
{
    /**
     * Get the template type.
     */
    public function type(): string
    {
        return 'page';
    }

    /**
     * Get the template icon.
     */
    public function icon(): ?string
    {
        return 'heroicon-o-document-text';
    }
}
