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

/** Customer quotes with name, role and optional photo. */
class TestimonialsBlock extends Block
{
    public static function type(): string
    {
        return 'testimonials';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedChatBubbleLeftRight;
    }

    public function fileFields(): array
    {
        return ['items.*.photo'];
    }

    public function defaults(): array
    {
        $item = fn () => ['quote' => static::t('defaults.quote'), 'name' => static::t('defaults.person'), 'role' => static::t('defaults.person_role')];

        return ['title' => static::t('defaults.testimonials_title'), 'columns' => 3, 'items' => [$item(), $item(), $item()]];
    }

    public function preview(array $data): ?string
    {
        return static::excerpt($data['title'] ?? null) ?? trans_choice('ferga-builder::builder.counts.items', count((array) ($data['items'] ?? [])));
    }

    public function schema(): array
    {
        return [
            TextInput::make('title')->label(static::t('fields.section_title')),
            ToggleButtons::make('columns')->label(static::t('fields.per_row'))->options([1 => '1', 2 => '2', 3 => '3'])->default(3)->inline(),
            Repeater::make('items')
                ->label(static::t('fields.items'))
                ->schema([
                    Textarea::make('quote')->label(static::t('fields.quote'))->rows(3)->required(),
                    TextInput::make('name')->label(static::t('fields.author'))->required(),
                    TextInput::make('role')->label(static::t('fields.role')),
                    $this->image('photo')->avatar(),
                ])
                ->itemLabel(fn (array $state) => $state['name'] ?? null)
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
            if (is_array($item) && filled($item['quote'] ?? null)) {
                $photo = $item['photo'] ?? null;
                $photo = is_array($photo) ? (array_values($photo)[0] ?? null) : $photo;
                $item['src'] = $builder->url(is_string($photo) ? $photo : null);
                $items[] = $item;
            }
        }

        $columns = (int) ($data['columns'] ?? 3);

        return ['items' => $items, 'columns' => in_array($columns, [1, 2, 3], true) ? $columns : 3];
    }
}
