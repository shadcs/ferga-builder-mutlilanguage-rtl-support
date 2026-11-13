<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Ferga\FilamentBuilder\Rendering\RenderContext;
use Ferga\FilamentBuilder\Support\Video;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;

class VideoBlock extends Block
{
    public static function type(): string
    {
        return 'video';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedPlayCircle;
    }

    public function group(): string
    {
        return 'media';
    }

    public function preview(array $data): ?string
    {
        return static::excerpt($data['caption'] ?? $data['url'] ?? null);
    }

    public function schema(): array
    {
        return [
            TextInput::make('url')
                ->label(static::t('fields.video_url'))
                ->url()
                ->required()
                ->placeholder('https://www.youtube.com/watch?v=…')
                ->rule(fn () => function (string $attribute, $value, $fail) {
                    if (filled($value) && ! Video::embedUrl($value)) {
                        $fail(static::t('validation.video_url'));
                    }
                }),
            TextInput::make('caption')->label(static::t('fields.caption')),
            Select::make('ratio')
                ->label(static::t('fields.ratio'))
                ->options(['16/9' => '16:9', '4/3' => '4:3', '1/1' => '1:1', '9/16' => '9:16'])
                ->default('16/9')
                ->selectablePlaceholder(false),
        ];
    }

    public function viewData(array $data, RenderContext $context): array
    {
        return [
            'embed' => Video::embedUrl($data['url'] ?? null),
            'ratio' => in_array($data['ratio'] ?? null, ['16/9', '4/3', '1/1', '9/16'], true) ? $data['ratio'] : '16/9',
        ];
    }
}
