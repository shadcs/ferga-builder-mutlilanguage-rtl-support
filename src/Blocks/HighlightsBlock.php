<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Ferga\FilamentBuilder\Support\Icons;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Icons\Heroicon;

class HighlightsBlock extends Block
{
    public static function type(): string
    {
        return 'highlights';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedSparkles;
    }

    public function defaults(): array
    {
        $item = fn (string $icon) => ['icon' => $icon, 'title' => static::t('defaults.highlight'), 'text' => static::t('defaults.text')];

        return ['columns' => 3, 'items' => [$item('heroicon-o-bolt'), $item('heroicon-o-shield-check'), $item('heroicon-o-heart')]];
    }

    public function preview(array $data): ?string
    {
        return static::excerpt($data['title'] ?? null) ?? trans_choice('ferga-builder::builder.counts.items', count((array) ($data['items'] ?? [])));
    }

    public function schema(): array
    {
        return [
            TextInput::make('title')->label(static::t('fields.section_title')),
            ToggleButtons::make('columns')->label(static::t('fields.per_row'))->options([2 => '2', 3 => '3', 4 => '4'])->default(3)->inline(),
            Repeater::make('items')
                ->label(static::t('fields.items'))
                ->schema([
                    Select::make('icon')
                        ->label(static::t('fields.icon'))
                        ->options(Icons::options())
                        ->searchable(),
                    TextInput::make('title')->label(static::t('fields.title'))->required(),
                    Textarea::make('text')->label(static::t('fields.text'))->rows(2),
                ])
                ->itemLabel(fn (array $state) => $state['title'] ?? null)
                ->collapsible()
                ->cloneable()
                ->minItems(1)
                ->defaultItems(3)
                ->addActionLabel(static::t('actions.add_item')),
        ];
    }
}
