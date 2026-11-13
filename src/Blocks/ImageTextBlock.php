<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Ferga\FilamentBuilder\FergaBuilder;
use Ferga\FilamentBuilder\Rendering\RenderContext;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Icons\Heroicon;

class ImageTextBlock extends Block
{
    public static function type(): string
    {
        return 'image_text';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedNewspaper;
    }

    public function group(): string
    {
        return 'media';
    }

    public function fileFields(): array
    {
        return ['image'];
    }

    public function defaults(): array
    {
        return ['title' => static::t('defaults.heading'), 'body' => '<p>'.e(static::t('defaults.text')).'</p>', 'image_position' => 'start'];
    }

    public function preview(array $data): ?string
    {
        return static::excerpt($data['title'] ?? $data['body'] ?? null);
    }

    public function schema(): array
    {
        return [
            $this->image('image'),
            TextInput::make('alt')->label(static::t('fields.alt')),
            TextInput::make('title')->label(static::t('fields.title')),
            RichEditor::make('body')->label(static::t('fields.body')),
            ToggleButtons::make('image_position')
                ->label(static::t('fields.image_position'))
                ->options(['start' => static::t('positions.start'), 'end' => static::t('positions.end')])
                ->default('start')
                ->inline(),
            TextInput::make('button_label')->label(static::t('fields.button_label')),
            TextInput::make('button_url')->label(static::t('fields.url')),
        ];
    }

    public function viewData(array $data, RenderContext $context): array
    {
        $body = $data['body'] ?? '';

        return [
            'src' => app(FergaBuilder::class)->url($data['image'] ?? null),
            'html' => (new RichTextBlock)->viewData(['body' => $body], $context)['html'],
        ];
    }
}
