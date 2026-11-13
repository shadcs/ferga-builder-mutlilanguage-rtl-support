<?php

namespace Ferga\FilamentBuilder\Concerns;

use Ferga\FilamentBuilder\FergaBuilder;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Convenience methods for models holding builder content.
 */
trait HasBuilderContent
{
    /** All locales: ['en' => [...], 'ar' => [...]]. */
    public function builderDocuments(string $field = 'content'): array
    {
        return app(FergaBuilder::class)->load($this, $field);
    }

    /** Blocks for a locale (falls back to the default locale). */
    public function builderBlocks(string $field = 'content', ?string $locale = null): array
    {
        return app(FergaBuilder::class)->blocksFor($this, $field, $locale);
    }

    public function setBuilderBlocks(string $locale, array $blocks, string $field = 'content'): static
    {
        $builder = app(FergaBuilder::class);
        $documents = $builder->load($this, $field);
        $documents[$locale] = $blocks;
        $builder->save($this, $field, $documents);

        return $this;
    }

    public function renderBuilder(string $field = 'content', ?string $locale = null): Htmlable
    {
        return app(FergaBuilder::class)->renderer()->renderDocuments($this->builderDocuments($field), $locale);
    }
}
