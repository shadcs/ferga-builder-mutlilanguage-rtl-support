<?php

namespace Ferga\FilamentBuilder\Templates;

use BackedEnum;
use Closure;
use Ferga\FilamentBuilder\FergaBuilder;
use Ferga\FilamentBuilder\Support\BlockTree;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

/**
 * A ready-made arrangement of blocks (a section or a whole page).
 *
 *   Template::make('pricing')
 *       ->label('Pricing')
 *       ->category(Template::SECTION)
 *       ->blocks(fn (TemplateCopy $t) => [
 *           ['type' => 'heading', 'data' => ['text' => $t('pricing_title')]],
 *       ]);
 *
 * The closure receives a TemplateCopy for the language being edited, so
 * sample text is inserted in that language.
 */
class Template
{
    public const SECTION = 'section';

    public const PAGE = 'page';

    protected Closure|array $blocks = [];

    protected string $category = self::SECTION;

    protected ?string $label = null;

    protected ?string $description = null;

    protected string|BackedEnum|null $icon = null;

    final public function __construct(public readonly string $key) {}

    public static function make(string $key): static
    {
        return new static($key);
    }

    public function blocks(Closure|array $blocks): static
    {
        $this->blocks = $blocks;

        return $this;
    }

    public function category(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function label(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function description(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function icon(string|BackedEnum|null $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function isPage(): bool
    {
        return $this->category === self::PAGE;
    }

    public function getLabel(): string
    {
        return $this->label ?? $this->translated('names', Str::headline($this->key));
    }

    public function getDescription(): ?string
    {
        return $this->description ?? $this->translated('descriptions', null);
    }

    public function getIcon(): string|BackedEnum
    {
        return $this->icon ?? ($this->isPage() ? Heroicon::OutlinedDocumentText : Heroicon::OutlinedSquare3Stack3d);
    }

    /**
     * Blocks for a content language, with fresh ids. Block types that are
     * not registered are left out.
     *
     * @return array<int, array>
     */
    public function build(?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $blocks = $this->blocks instanceof Closure ? ($this->blocks)(new TemplateCopy($locale), $locale) : $this->blocks;
        $registry = app(FergaBuilder::class)->registry();

        $blocks = array_values(array_filter((array) $blocks, fn ($block) => is_array($block) && $registry->has($block['type'] ?? null)));

        foreach ($blocks as $i => $block) {
            if (isset($block['data']['columns']) && is_array($block['data']['columns'])) {
                foreach ($block['data']['columns'] as $c => $column) {
                    $blocks[$i]['data']['columns'][$c]['blocks'] = array_values(array_filter(
                        (array) ($column['blocks'] ?? []),
                        fn ($nested) => is_array($nested) && $registry->has($nested['type'] ?? null),
                    ));
                }
            }
        }

        return BlockTree::refreshIds($blocks);
    }

    /** Block types used (top level), for previews. @return array<int, string> */
    public function blockTypes(?string $locale = null): array
    {
        return array_column($this->build($locale), 'type');
    }

    protected function translated(string $group, ?string $fallback): ?string
    {
        $key = "ferga-builder::templates.{$group}.{$this->key}";
        $value = __($key);

        return $value === $key ? $fallback : $value;
    }
}
