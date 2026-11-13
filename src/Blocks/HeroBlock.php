<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Ferga\FilamentBuilder\FergaBuilder;
use Ferga\FilamentBuilder\Rendering\RenderContext;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;

/** Page header: eyebrow, title, text, up to two buttons, optional image. */
class HeroBlock extends Block
{
    public static function type(): string
    {
        return 'hero';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedRectangleGroup;
    }

    public function group(): string
    {
        return 'sections';
    }

    public function nestable(): bool
    {
        return false;
    }

    public function fileFields(): array
    {
        return ['image'];
    }

    public function defaults(): array
    {
        return [
            'layout' => 'center',
            'title' => static::t('defaults.hero_title'),
            'text' => static::t('defaults.hero_text'),
            'primary_label' => static::t('defaults.button'),
            'primary_url' => '#',
        ];
    }

    public function preview(array $data): ?string
    {
        return static::excerpt($data['title'] ?? null);
    }

    public function schema(): array
    {
        return [
            ToggleButtons::make('layout')
                ->label(static::t('fields.layout'))
                ->options([
                    'center' => static::t('layouts.center'),
                    'split' => static::t('layouts.split'),
                    'cover' => static::t('layouts.cover'),
                ])
                ->default('center')
                ->inline(),
            TextInput::make('eyebrow')->label(static::t('fields.eyebrow')),
            TextInput::make('title')->label(static::t('fields.title'))->required(),
            Textarea::make('text')->label(static::t('fields.text'))->rows(3),
            Grid::make(2)->schema([
                TextInput::make('primary_label')->label(static::t('fields.primary_button')),
                TextInput::make('primary_url')->label(static::t('fields.url')),
                TextInput::make('secondary_label')->label(static::t('fields.secondary_button')),
                TextInput::make('secondary_url')->label(static::t('fields.url')),
            ]),
            $this->image('image'),
            TextInput::make('alt')->label(static::t('fields.alt')),
        ];
    }

    public function viewData(array $data, RenderContext $context): array
    {
        $layout = in_array($data['layout'] ?? 'center', ['center', 'split', 'cover'], true) ? ($data['layout'] ?? 'center') : 'center';

        return [
            'layout' => $layout,
            'src' => app(FergaBuilder::class)->url(is_string($data['image'] ?? null) ? $data['image'] : null),
        ];
    }
}
