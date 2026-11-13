<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;

class QuoteBlock extends Block
{
    public static function type(): string
    {
        return 'quote';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedChatBubbleBottomCenterText;
    }

    public function group(): string
    {
        return 'text';
    }

    public function defaults(): array
    {
        return ['text' => static::t('defaults.quote')];
    }

    public function preview(array $data): ?string
    {
        return static::excerpt($data['text'] ?? null);
    }

    public function schema(): array
    {
        return [
            Textarea::make('text')->label(static::t('fields.quote'))->rows(3)->required(),
            TextInput::make('author')->label(static::t('fields.author')),
            TextInput::make('role')->label(static::t('fields.role')),
        ];
    }
}
