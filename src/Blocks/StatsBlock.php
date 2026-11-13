<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;

class StatsBlock extends Block
{
    public static function type(): string
    {
        return 'stats';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedChartBar;
    }

    public function defaults(): array
    {
        return ['items' => [
            ['value' => '250+', 'label' => static::t('defaults.stat')],
            ['value' => '15', 'label' => static::t('defaults.stat')],
            ['value' => '99%', 'label' => static::t('defaults.stat')],
        ]];
    }

    public function preview(array $data): ?string
    {
        return static::excerpt(implode(' · ', array_filter(array_column((array) ($data['items'] ?? []), 'value'))));
    }

    public function schema(): array
    {
        return [
            Repeater::make('items')
                ->label(static::t('fields.items'))
                ->schema([
                    TextInput::make('value')->label(static::t('fields.value'))->required()->placeholder('250+'),
                    TextInput::make('label')->label(static::t('fields.label'))->required(),
                ])
                ->columns(2)
                ->minItems(1)
                ->maxItems(6)
                ->defaultItems(3)
                ->addActionLabel(static::t('actions.add_item')),
        ];
    }
}
