<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Ferga\FilamentBuilder\FergaBuilder;
use Ferga\FilamentBuilder\Rendering\RenderContext;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Icons\Heroicon;

class GalleryBlock extends Block
{
    public static function type(): string
    {
        return 'gallery';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedSquares2x2;
    }

    public function group(): string
    {
        return 'media';
    }

    public function fileFields(): array
    {
        return ['images'];
    }

    public function defaults(): array
    {
        return ['columns' => 3, 'images' => []];
    }

    public function preview(array $data): ?string
    {
        return trans_choice('ferga-builder::builder.counts.images', count((array) ($data['images'] ?? [])));
    }

    public function schema(): array
    {
        return [
            $this->image('images')->multiple()->reorderable()->appendFiles()->panelLayout('grid')->required(),
            TextInput::make('alt')->label(static::t('fields.alt')),
            ToggleButtons::make('columns')
                ->label(static::t('fields.per_row'))
                ->options([2 => '2', 3 => '3', 4 => '4'])
                ->default(3)
                ->inline(),
        ];
    }

    public function viewData(array $data, RenderContext $context): array
    {
        $builder = app(FergaBuilder::class);

        return [
            'images' => array_values(array_filter(array_map(fn ($path) => is_string($path) ? $builder->url($path) : null, array_values((array) ($data['images'] ?? []))))),
            'columns' => in_array($columns = (int) ($data['columns'] ?? 3), [2, 3, 4], true) ? $columns : 3,
        ];
    }
}
