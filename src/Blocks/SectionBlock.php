<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Ferga\FilamentBuilder\FergaBuilder;
use Ferga\FilamentBuilder\Rendering\RenderContext;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;

/**
 * Layout container: a row of columns, each holding blocks.
 * data: layout, gap, valign, columns: [{blocks: [...]}, …]
 */
class SectionBlock extends Block
{
    /** layout => relative column widths */
    public const LAYOUTS = [
        '1' => [1],
        '1-1' => [1, 1],
        '1-2' => [1, 2],
        '2-1' => [2, 1],
        '1-1-1' => [1, 1, 1],
        '1-2-1' => [1, 2, 1],
        '1-1-1-1' => [1, 1, 1, 1],
    ];

    public static function type(): string
    {
        return 'section';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedViewColumns;
    }

    public function group(): string
    {
        return 'layout';
    }

    public function nestable(): bool
    {
        return false;
    }

    public function defaults(): array
    {
        return ['layout' => '1-1', 'gap' => 'md', 'valign' => 'start', 'columns' => [['blocks' => []], ['blocks' => []]]];
    }

    public function preview(array $data): ?string
    {
        return str_replace('-', ' : ', (string) ($data['layout'] ?? '1'));
    }

    public static function layoutOptions(): array
    {
        return collect(array_keys(static::LAYOUTS))
            ->mapWithKeys(fn ($layout) => [$layout => str_replace('-', ' : ', $layout)])
            ->all();
    }

    public static function columnCount(?string $layout): int
    {
        return count(static::LAYOUTS[$layout] ?? [1, 1]);
    }

    /** Resize columns to a layout; blocks of removed columns move into the last kept column. */
    public static function applyLayout(array $data, string $layout): array
    {
        if (! isset(static::LAYOUTS[$layout])) {
            return $data;
        }

        $count = static::columnCount($layout);
        $columns = array_values((array) ($data['columns'] ?? []));

        while (count($columns) < $count) {
            $columns[] = ['blocks' => []];
        }

        if (count($columns) > $count) {
            $overflow = array_splice($columns, $count);

            foreach ($overflow as $column) {
                $columns[$count - 1]['blocks'] = [...($columns[$count - 1]['blocks'] ?? []), ...($column['blocks'] ?? [])];
            }
        }

        $data['layout'] = $layout;
        $data['columns'] = $columns;

        return $data;
    }

    protected function settingsSchema(): array
    {
        return [
            Select::make('layout')
                ->label(static::t('fields.layout'))
                ->options(static::layoutOptions())
                ->default('1-1')
                ->selectablePlaceholder(false)
                ->required(),
            Select::make('gap')
                ->label(static::t('fields.gap'))
                ->options(['none' => static::t('sizes.none'), 'sm' => static::t('sizes.sm'), 'md' => static::t('sizes.md'), 'lg' => static::t('sizes.lg')])
                ->default('md')
                ->selectablePlaceholder(false),
            Select::make('valign')
                ->label(static::t('fields.valign'))
                ->options(['start' => static::t('positions.top'), 'center' => static::t('positions.middle'), 'end' => static::t('positions.bottom')])
                ->default('start')
                ->selectablePlaceholder(false),
        ];
    }

    public function schema(): array
    {
        $nested = array_map(fn ($block) => $block->toFilamentBlock(), array_values(app(FergaBuilder::class)->registry()->nestable()));

        return [
            ...$this->settingsSchema(),
            Repeater::make('columns')
                ->label(static::t('fields.columns'))
                ->schema([
                    Builder::make('blocks')
                        ->hiddenLabel()
                        ->blocks($nested)
                        ->addActionLabel(static::t('actions.add_block'))
                        ->collapsible()
                        ->cloneable(),
                ])
                ->defaultItems(2)
                ->minItems(1)
                ->maxItems(4)
                ->grid(['default' => 1, 'lg' => 2])
                ->addActionLabel(static::t('actions.add_column'))
                ->columnSpanFull(),
        ];
    }

    public function canvasSchema(): array
    {
        return $this->settingsSchema();
    }

    public function viewData(array $data, RenderContext $context): array
    {
        $layout = (string) ($data['layout'] ?? '1-1');
        $columns = array_values((array) ($data['columns'] ?? []));
        $widths = static::LAYOUTS[$layout] ?? array_fill(0, max(1, count($columns)), 1);

        if (count($widths) !== count($columns)) {
            $widths = array_fill(0, max(1, count($columns)), 1);
        }

        return [
            'columns' => $columns,
            'template' => implode(' ', array_map(fn ($w) => "minmax(0, {$w}fr)", $widths)),
            'renderer' => app(FergaBuilder::class)->renderer(),
        ];
    }
}
