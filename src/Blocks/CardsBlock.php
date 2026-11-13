<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Ferga\FilamentBuilder\FergaBuilder;
use Ferga\FilamentBuilder\Rendering\RenderContext;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Icons\Heroicon;

/** Grid of cards: image, title, text and an optional link. */
class CardsBlock extends Block
{
    public static function type(): string
    {
        return 'cards';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedRectangleStack;
    }

    public function fileFields(): array
    {
        return ['items.*.image'];
    }

    public function defaults(): array
    {
        $item = fn () => ['title' => static::t('defaults.card_title'), 'text' => static::t('defaults.text'), 'link_label' => static::t('defaults.read_more'), 'url' => '#'];

        return ['columns' => 3, 'items' => [$item(), $item(), $item()]];
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
                    $this->image('image'),
                    TextInput::make('title')->label(static::t('fields.title'))->required(),
                    Textarea::make('text')->label(static::t('fields.text'))->rows(2),
                    TextInput::make('link_label')->label(static::t('fields.link_label')),
                    TextInput::make('url')->label(static::t('fields.url')),
                ])
                ->itemLabel(fn (array $state) => $state['title'] ?? null)
                ->collapsible()
                ->cloneable()
                ->minItems(1)
                ->addActionLabel(static::t('actions.add_item')),
        ];
    }

    public function viewData(array $data, RenderContext $context): array
    {
        $builder = app(FergaBuilder::class);
        $items = [];

        foreach ((array) ($data['items'] ?? []) as $item) {
            if (is_array($item) && filled($item['title'] ?? null)) {
                $image = $item['image'] ?? null;
                $image = is_array($image) ? (array_values($image)[0] ?? null) : $image;
                $item['src'] = $builder->url(is_string($image) ? $image : null);
                $items[] = $item;
            }
        }

        $columns = (int) ($data['columns'] ?? 3);

        return ['items' => $items, 'columns' => in_array($columns, [2, 3, 4], true) ? $columns : 3];
    }
}
