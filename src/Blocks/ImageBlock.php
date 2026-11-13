<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Ferga\FilamentBuilder\FergaBuilder;
use Ferga\FilamentBuilder\Rendering\RenderContext;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;

class ImageBlock extends Block
{
    public static function type(): string
    {
        return 'image';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedPhoto;
    }

    public function group(): string
    {
        return 'media';
    }

    public function fileFields(): array
    {
        return ['image'];
    }

    public function preview(array $data): ?string
    {
        return static::excerpt($data['caption'] ?? $data['alt'] ?? null);
    }

    public function schema(): array
    {
        return [
            $this->image('image')->required(),
            TextInput::make('alt')->label(static::t('fields.alt')),
            TextInput::make('caption')->label(static::t('fields.caption')),
            TextInput::make('link')->label(static::t('fields.link'))->placeholder('https://… or /page'),
        ];
    }

    public function viewData(array $data, RenderContext $context): array
    {
        return ['src' => app(FergaBuilder::class)->url($data['image'] ?? null)];
    }
}
