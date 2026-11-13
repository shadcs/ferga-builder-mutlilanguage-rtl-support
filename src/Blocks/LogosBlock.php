<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Ferga\FilamentBuilder\FergaBuilder;
use Ferga\FilamentBuilder\Rendering\RenderContext;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Support\Icons\Heroicon;

/** Row of client/partner logos. */
class LogosBlock extends Block
{
    public static function type(): string
    {
        return 'logos';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedBuildingOffice2;
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
        return ['title' => static::t('defaults.logos_title'), 'grayscale' => true, 'images' => []];
    }

    public function preview(array $data): ?string
    {
        return trans_choice('ferga-builder::builder.counts.images', count((array) ($data['images'] ?? [])));
    }

    public function schema(): array
    {
        return [
            TextInput::make('title')->label(static::t('fields.section_title')),
            $this->image('images')->multiple()->reorderable()->appendFiles()->panelLayout('grid'),
            Toggle::make('grayscale')->label(static::t('fields.grayscale'))->default(true),
        ];
    }

    public function viewData(array $data, RenderContext $context): array
    {
        $builder = app(FergaBuilder::class);

        return [
            'images' => array_values(array_filter(array_map(fn ($path) => is_string($path) ? $builder->url($path) : null, array_values((array) ($data['images'] ?? []))))),
        ];
    }
}
