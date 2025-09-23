<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager\Contracts;

use Illuminate\Database\Eloquent\Model;

interface TemplateContract
{
    /**
     * Get the template name.
     */
    public function name(): string;

    /**
     * Get the template type (page or region).
     */
    public function type(): string;

    /**
     * Get the form fields for this template.
     *
     * @return array<int, mixed>
     */
    public function fields(): array;

    /**
     * Resolve the data for frontend consumption.
     *
     * @return array<string, mixed>
     */
    public function resolve(Model $model, ?string $locale = null): array;

    /**
     * Get the path suffix for this template (pages only).
     */
    public function pathSuffix(): ?string;

    /**
     * Whether this template can only have a single instance.
     */
    public function unique(): bool;

    /**
     * Get the template description.
     */
    public function description(): ?string;

    /**
     * Get the template icon.
     */
    public function icon(): ?string;
}
