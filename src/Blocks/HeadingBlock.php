<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;

class HeadingBlock extends Block
{
    public static function type(): string
    {
        return 'heading';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedH1;
    }

    public function group(): string
    {
        return 'text';
    }

    public function defaults(): array
    {
        return ['text' => static::t('defaults.heading'), 'level' => 'h2'];
    }

    public function preview(array $data): ?string
    {
        return static::excerpt($data['text'] ?? null);
    }

    public function schema(): array
    {
        return [
            TextInput::make('eyebrow')->label(static::t('fields.eyebrow')),
            TextInput::make('text')->label(static::t('fields.text'))->required(),
            TextInput::make('subtitle')->label(static::t('fields.subtitle')),
            Select::make('level')
                ->label(static::t('fields.level'))
                ->options(['h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4'])
                ->default('h2')
                ->selectablePlaceholder(false),
        ];
    }
}
