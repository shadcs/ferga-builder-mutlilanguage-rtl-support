<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Icons\Heroicon;

class DividerBlock extends Block
{
    public static function type(): string
    {
        return 'divider';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedMinus;
    }

    public function group(): string
    {
        return 'layout';
    }

    public function defaults(): array
    {
        return ['variant' => 'solid'];
    }

    public function schema(): array
    {
        return [
            ToggleButtons::make('variant')
                ->label(static::t('fields.variant'))
                ->options(['solid' => static::t('variants.solid'), 'dashed' => static::t('variants.dashed'), 'dotted' => static::t('variants.dotted')])
                ->default('solid')
                ->inline(),
        ];
    }
}
