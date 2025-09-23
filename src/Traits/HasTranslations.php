<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager\Traits;

use IamGerwin\FilamentPageManager\Facades\FilamentPageManager;

trait HasTranslations
{
    public function getAttributeValue($key)
    {
        if (! $this->isTranslatableAttribute($key)) {
            return parent::getAttributeValue($key);
        }

        return $this->getTranslation($key, app()->getLocale());
    }

    public function setAttribute($key, $value)
    {
        if (! $this->isTranslatableAttribute($key)) {
            return parent::setAttribute($key, $value);
        }

        if (is_array($value) && ! $this->isTranslatedArray($value)) {
            return $this->setTranslation($key, app()->getLocale(), $value);
        }

        return parent::setAttribute($key, $value);
    }

    public function getTranslation(string $key, string $locale, $default = null)
    {
        $translations = $this->getTranslations($key);

        if (! isset($translations[$locale])) {
            $fallbackLocale = config('filament-page-manager.default_locale', 'en');

            if (! isset($translations[$fallbackLocale])) {
                return $default;
            }

            return $translations[$fallbackLocale];
        }

        return $translations[$locale];
    }

    public function getTranslations(string $key): array
    {
        $value = parent::getAttributeValue($key);

        if (! is_array($value)) {
            return [];
        }

        return $value;
    }

    public function setTranslation(string $key, string $locale, $value): self
    {
        $translations = $this->getTranslations($key);
        $translations[$locale] = $value;

        parent::setAttribute($key, $translations);

        return $this;
    }

    public function setTranslations(string $key, array $translations): self
    {
        parent::setAttribute($key, $translations);

        return $this;
    }

    public function forgetTranslation(string $key, string $locale): self
    {
        $translations = $this->getTranslations($key);
        unset($translations[$locale]);

        parent::setAttribute($key, $translations);

        return $this;
    }

    public function getTranslatedLocales(string $key): array
    {
        return array_keys($this->getTranslations($key));
    }

    public function isTranslatableAttribute(string $key): bool
    {
        return in_array($key, $this->getTranslatableAttributes());
    }

    public function getTranslatableAttributes(): array
    {
        return $this->translatable ?? [];
    }

    public function getLocales(): array
    {
        return FilamentPageManager::getLocales();
    }

    public function hasTranslation(string $key, string $locale): bool
    {
        return isset($this->getTranslations($key)[$locale]);
    }

    public function replicate(?array $except = null)
    {
        $clone = parent::replicate($except);

        foreach ($this->getTranslatableAttributes() as $attribute) {
            $clone->setTranslations($attribute, $this->getTranslations($attribute));
        }

        return $clone;
    }

    protected function isTranslatedArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        $locales = array_keys(FilamentPageManager::getLocales());

        foreach (array_keys($array) as $key) {
            if (! in_array($key, $locales)) {
                return false;
            }
        }

        return true;
    }
}
