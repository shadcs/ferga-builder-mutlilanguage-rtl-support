<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Ferga\FilamentBuilder\Rendering\RenderContext;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Support\Icons\Heroicon;

class RichTextBlock extends Block
{
    public static function type(): string
    {
        return 'rich_text';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedBars3BottomLeft;
    }

    public function group(): string
    {
        return 'text';
    }

    public function defaults(): array
    {
        return ['body' => '<p>'.e(static::t('defaults.text')).'</p>'];
    }

    public function preview(array $data): ?string
    {
        return static::excerpt($data['body'] ?? null);
    }

    public function schema(): array
    {
        return [
            RichEditor::make('body')->label(static::t('fields.body'))->required(),
        ];
    }

    public function viewData(array $data, RenderContext $context): array
    {
        $body = $data['body'] ?? '';

        if (is_array($body)) {
            $body = class_exists(RichContentRenderer::class)
                ? RichContentRenderer::make($body)->toHtml()
                : '';
        }

        return ['html' => (string) $body];
    }
}
