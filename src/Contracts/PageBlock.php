<?php

namespace Ferga\FilamentBuilder\Contracts;

use BackedEnum;
use Ferga\FilamentBuilder\Rendering\RenderContext;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;

/**
 * A block type that can be placed on a page.
 *
 * Stored shape of one block: ['id' => '…', 'type' => static::type(), 'data' => [...]].
 */
interface PageBlock
{
    /** Unique machine name, stored in the JSON (e.g. "heading"). */
    public static function type(): string;

    /** Human label shown in the editors (already translated). */
    public function label(): string;

    /** Heroicon enum or icon alias shown in the block picker. */
    public function icon(): string|BackedEnum;

    /** Group in the block picker, e.g. "layout", "text", "media". */
    public function group(): string;

    /**
     * Filament components editing the block's `data`.
     *
     * @return array<int, Component|Field>
     */
    public function schema(): array;

    /** Blade view rendering the block on the public site and on the canvas. */
    public function view(): string;

    /**
     * Keys inside `data` that hold file paths (string or list of strings).
     * Used to resolve public URLs and to collect files for cleanup.
     *
     * @return array<int, string>
     */
    public function fileFields(): array;

    /** Default `data` for a freshly inserted block. */
    public function defaults(): array;

    /** Short text describing the content of a block (outline + form item label). */
    public function preview(array $data): ?string;

    /** Whether the block can be placed inside a section column. */
    public function nestable(): bool;

    /** Variables passed to the block's view. */
    public function viewData(array $data, RenderContext $context): array;

    public function render(array $block, RenderContext $context): Htmlable|string;
}
