<?php

namespace Ferga\FilamentBuilder\Templates;

/**
 * Sample text for templates in one language: $t('hero_title').
 * Looks up ferga-builder::templates.copy.{key} in that language (falls back
 * to the app fallback locale).
 */
class TemplateCopy
{
    public function __construct(public readonly string $locale) {}

    public function __invoke(string $key, array $replace = []): string
    {
        $line = __("ferga-builder::templates.copy.{$key}", $replace, $this->locale);

        return is_string($line) ? $line : $key;
    }
}
