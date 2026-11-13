<?php

namespace Ferga\FilamentBuilder\Rendering;

use Ferga\FilamentBuilder\FergaBuilder;

class RenderContext
{
    public function __construct(
        public readonly string $locale,
        public readonly bool $editing = false,
        public readonly int $depth = 0,
        public readonly string $path = '',
        /** Canvas-only state (selected path, chrome direction). */
        public readonly array $editor = [],
    ) {}

    public function direction(): string
    {
        return app(FergaBuilder::class)->direction($this->locale);
    }

    public function isRtl(): bool
    {
        return app(FergaBuilder::class)->isRtl($this->locale);
    }

    public function lang(): string
    {
        return app(FergaBuilder::class)->htmlLang($this->locale);
    }

    public function nested(string $path): static
    {
        return new static($this->locale, $this->editing, $this->depth + 1, $path, $this->editor);
    }

    public function at(string $path): static
    {
        return new static($this->locale, $this->editing, $this->depth, $path, $this->editor);
    }
}
