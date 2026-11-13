<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Icons\Heroicon;

class SpacerBlock extends Block
{
    public static function type(): string
    {
        return 'spacer';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedArrowsUpDown;
    }

    public function group(): string
    {
        return 'layout';
    }

    public function defaults(): array
    {
        return ['height' => 'md'];
    }

    public function preview(array $data): ?string
    {
        return static::t('sizes.'.($data['height'] ?? 'md'));
    }

    public function schema(): array
    {
        return [
            ToggleButtons::make('height')
                ->label(static::t('fields.height'))
                ->options(['sm' => static::t('sizes.sm'), 'md' => static::t('sizes.md'), 'lg' => static::t('sizes.lg'), 'xl' => static::t('sizes.xl')])
                ->default('md')
                ->inline(),
        ];
    }
}
