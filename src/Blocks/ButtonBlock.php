<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Icons\Heroicon;

class ButtonBlock extends Block
{
    public static function type(): string
    {
        return 'button';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedCursorArrowRays;
    }

    public function group(): string
    {
        return 'text';
    }

    public function defaults(): array
    {
        return ['label' => static::t('defaults.button'), 'url' => '#', 'variant' => 'primary', 'arrow' => true];
    }

    public function preview(array $data): ?string
    {
        return static::excerpt($data['label'] ?? null);
    }

    public function schema(): array
    {
        return [
            TextInput::make('label')->label(static::t('fields.label'))->required(),
            TextInput::make('url')->label(static::t('fields.url'))->required()->placeholder('https://… or /contact'),
            ToggleButtons::make('variant')
                ->label(static::t('fields.variant'))
                ->options(['primary' => static::t('variants.primary'), 'outline' => static::t('variants.outline'), 'link' => static::t('variants.link')])
                ->default('primary')
                ->inline(),
            Toggle::make('arrow')->label(static::t('fields.arrow'))->default(true),
            Toggle::make('new_tab')->label(static::t('fields.new_tab')),
        ];
    }
}
