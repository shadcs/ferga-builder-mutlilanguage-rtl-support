<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Support\Icons\Heroicon;

/** Questions and answers (native <details>, works without JavaScript). */
class FaqBlock extends Block
{
    public static function type(): string
    {
        return 'faq';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedQuestionMarkCircle;
    }

    public function defaults(): array
    {
        $item = fn () => ['question' => static::t('defaults.question'), 'answer' => static::t('defaults.answer')];

        return ['title' => static::t('defaults.faq_title'), 'open_first' => true, 'items' => [$item(), $item(), $item()]];
    }

    public function preview(array $data): ?string
    {
        return static::excerpt($data['title'] ?? null) ?? trans_choice('ferga-builder::builder.counts.items', count((array) ($data['items'] ?? [])));
    }

    public function schema(): array
    {
        return [
            TextInput::make('title')->label(static::t('fields.section_title')),
            Toggle::make('open_first')->label(static::t('fields.open_first')),
            Repeater::make('items')
                ->label(static::t('fields.items'))
                ->schema([
                    TextInput::make('question')->label(static::t('fields.question'))->required(),
                    Textarea::make('answer')->label(static::t('fields.answer'))->rows(3)->required(),
                ])
                ->itemLabel(fn (array $state) => $state['question'] ?? null)
                ->collapsible()
                ->cloneable()
                ->minItems(1)
                ->addActionLabel(static::t('actions.add_item')),
        ];
    }
}
