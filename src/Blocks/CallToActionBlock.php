<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;

/** Banner with a title, short text and buttons. */
class CallToActionBlock extends Block
{
    public static function type(): string
    {
        return 'call_to_action';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedMegaphone;
    }

    public function group(): string
    {
        return 'sections';
    }

    public function defaults(): array
    {
        return [
            'title' => static::t('defaults.cta_title'),
            'text' => static::t('defaults.cta_text'),
            'primary_label' => static::t('defaults.button'),
            'primary_url' => '#',
            'style' => ['background' => 'primary', 'align' => 'center', 'spacing' => 'lg'],
        ];
    }

    public function preview(array $data): ?string
    {
        return static::excerpt($data['title'] ?? null);
    }

    public function schema(): array
    {
        return [
            TextInput::make('title')->label(static::t('fields.title'))->required(),
            Textarea::make('text')->label(static::t('fields.text'))->rows(2),
            Grid::make(2)->schema([
                TextInput::make('primary_label')->label(static::t('fields.primary_button')),
                TextInput::make('primary_url')->label(static::t('fields.url')),
                TextInput::make('secondary_label')->label(static::t('fields.secondary_button')),
                TextInput::make('secondary_url')->label(static::t('fields.url')),
            ]),
        ];
    }
}
