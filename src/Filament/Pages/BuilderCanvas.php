<?php

namespace Ferga\FilamentBuilder\Filament\Pages;

use Ferga\FilamentBuilder\Blocks\Block;
use Ferga\FilamentBuilder\Blocks\SectionBlock;
use Ferga\FilamentBuilder\Contracts\ContentStore;
use Ferga\FilamentBuilder\Contracts\PageBlock;
use Ferga\FilamentBuilder\FergaBuilder;
use Ferga\FilamentBuilder\Rendering\RenderContext;
use Ferga\FilamentBuilder\Support\BlockTree;
use Ferga\FilamentBuilder\Support\StyleTokens;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Group;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Cache;

/**
 * Full-screen visual editor for one record's builder content.
 *
 *   class PageBuilderCanvas extends BuilderCanvas
 *   {
 *       protected static string $resource = PageResource::class;
 *   }
 *
 *   // PageResource::getPages()
 *   'builder' => PageBuilderCanvas::route('/{record}/builder'),
 */
abstract class BuilderCanvas extends Page
{
    use InteractsWithRecord;

    protected string $view = 'ferga-builder::canvas.page';

    /** Model attribute / store field holding the content. */
    protected static string $contentField = 'content';

    protected static bool $shouldRegisterNavigation = false;

    /** @var array<string, array<int, array>> locale => blocks */
    public array $documents = [];

    public string $activeLocale = '';

    public ?string $selected = null;

    public string $device = 'desktop';

    public bool $isDirty = false;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        $builder = $this->builder();
        $loaded = $builder->load($this->getRecord(), static::$contentField, $this->getContentStore());

        foreach ($this->getLocales() as $locale => $label) {
            $this->documents[$locale] = BlockTree::clean($loaded[$locale] ?? []);
        }

        $this->activeLocale = array_key_exists($builder->defaultLocale(), $this->documents)
            ? $builder->defaultLocale()
            : (string) array_key_first($this->documents);

        $this->forgetHistory();
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
    }

    protected function getContentStore(): ?ContentStore
    {
        return null;
    }

    /** @return array<string, string> */
    public function getLocales(): array
    {
        return $this->builder()->locales();
    }

    public function getTitle(): string|Htmlable
    {
        return __('ferga-builder::builder.canvas.title').' · '.$this->getRecordTitle();
    }

    public static function getNavigationLabel(): string
    {
        return __('ferga-builder::builder.canvas.title');
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getBackUrl(): ?string
    {
        $resource = static::getResource();

        foreach (['edit', 'view', 'index'] as $page) {
            if ($resource::hasPage($page)) {
                return $page === 'index' ? $resource::getUrl('index') : $resource::getUrl($page, ['record' => $this->getRecord()]);
            }
        }

        return null;
    }

    protected function builder(): FergaBuilder
    {
        return app(FergaBuilder::class);
    }

    // ------------------------------------------------------------ state

    /** Blocks of the active locale. */
    public function getBlocks(): array
    {
        return $this->documents[$this->activeLocale] ?? [];
    }

    public function getBlockType(?string $type): ?PageBlock
    {
        return $this->builder()->registry()->get($type);
    }

    public function getSelectedBlock(): ?array
    {
        return $this->selected === null ? null : BlockTree::get($this->getBlocks(), $this->selected);
    }

    public function getRenderContext(): RenderContext
    {
        return new RenderContext($this->activeLocale, editing: true, editor: [
            'selected' => $this->selected,
            'chromeDir' => $this->builder()->direction(app()->getLocale()),
        ]);
    }

    /** Outline rows: [path, depth, label, type, icon]. */
    public function getOutline(): array
    {
        $rows = [];

        BlockTree::walk($this->getBlocks(), function (array $block, string $path) use (&$rows) {
            $type = $this->getBlockType($block['type'] ?? null);
            $depth = intdiv(substr_count($path, '.'), 2);
            $preview = $type?->preview((array) ($block['data'] ?? []));

            $rows[] = [
                'path' => $path,
                'depth' => $depth,
                'label' => ($type?->label() ?? $block['type']).(filled($preview) ? ' · '.$preview : ''),
                'icon' => $type?->icon(),
                'known' => (bool) $type,
            ];
        });

        return $rows;
    }

    protected function commit(array $blocks, ?string $select = null): void
    {
        $this->pushHistory();
        $this->documents[$this->activeLocale] = array_values($blocks);
        $this->isDirty = true;
        $this->selected = $select ?? (($this->selected !== null && BlockTree::get($blocks, $this->selected)) ? $this->selected : null);
    }

    // ------------------------------------------------------------ operations

    public function insertBlock(string $type, ?string $container = null, ?int $index = null): void
    {
        $blockType = $this->getBlockType($type);

        if (! $blockType) {
            return;
        }

        $blocks = $this->getBlocks();

        if ($container === null) {
            // Click-to-insert: after the selected block, else at the end.
            if ($this->selected !== null && BlockTree::get($blocks, $this->selected)) {
                [$container, $selectedIndex] = BlockTree::split($this->selected);
                $index = $selectedIndex + 1;
            } else {
                $container = '';
            }
        }

        if ($container !== '' && ! $blockType->nestable()) {
            // Sections only live at the top level: insert after the parent section.
            $container = '';
            $index = ((int) explode('.', (string) $this->selected)[0]) + 1;
        }

        $list = BlockTree::getContainer($blocks, $container);

        if ($list === null) {
            return;
        }

        $index = $index === null ? count($list) : max(0, min($index, count($list)));
        $block = ['id' => BlockTree::newId(), 'type' => $type, 'data' => $blockType->defaults()];

        $this->commit(
            BlockTree::insert($blocks, $container, $index, $block),
            $container === '' ? (string) $index : "{$container}.{$index}",
        );
    }

    /**
     * Insert a ready-made template (sample text in the language being
     * edited). Templates always go to the top level: after the selected
     * block's top-level ancestor, at a top-level drop slot, or at the end.
     */
    public function insertTemplate(string $key, ?string $container = null, ?int $index = null): void
    {
        $template = $this->builder()->templates()->get($key);

        if (! $template) {
            return;
        }

        $new = $template->build($this->activeLocale);

        if ($new === []) {
            return;
        }

        $blocks = $this->getBlocks();

        if ($container === null) {
            $index = ($this->selected !== null && BlockTree::get($blocks, $this->selected))
                ? ((int) explode('.', $this->selected)[0]) + 1
                : count($blocks);
        } elseif ($container !== '') {
            $index = ((int) explode('.', $container)[0]) + 1;
        }

        $index = max(0, min((int) $index, count($blocks)));
        array_splice($blocks, $index, 0, $new);

        $this->commit($blocks, (string) $index);
    }

    public function moveBlock(string $path, string $container, int $index): void
    {
        $before = $this->getBlocks();
        $after = BlockTree::move($before, $path, $container, $index, function (array $block) {
            return (bool) $this->getBlockType($block['type'] ?? null)?->nestable();
        });

        if ($after !== $before) {
            $this->commit($after, null);
            $this->selected = null;
        }
    }

    public function moveBlockBy(string $path, int $offset): void
    {
        [$container, $index] = BlockTree::split($path);
        $list = BlockTree::getContainer($this->getBlocks(), $container) ?? [];
        $target = $index + $offset;

        if ($target < 0 || $target >= count($list)) {
            return;
        }

        $this->moveBlock($path, $container, $offset > 0 ? $target + 1 : $target);
        $this->selected = $container === '' ? (string) $target : "{$container}.{$target}";
    }

    public function removeBlock(string $path): void
    {
        [$blocks, $removed] = BlockTree::remove($this->getBlocks(), $path);

        if ($removed) {
            $this->commit($blocks);
            $this->selected = null;
        }
    }

    public function duplicateBlock(string $path): void
    {
        if (! BlockTree::get($this->getBlocks(), $path)) {
            return;
        }

        [$container, $index] = BlockTree::split($path);

        $this->commit(
            BlockTree::duplicate($this->getBlocks(), $path),
            $container === '' ? (string) ($index + 1) : "{$container}.".($index + 1),
        );
    }

    public function selectBlock(?string $path): void
    {
        $this->selected = ($path !== null && BlockTree::get($this->getBlocks(), $path)) ? $path : null;
    }

    public function setStyle(string $path, string $token, string $value): void
    {
        if (! StyleTokens::isValid($token, $value) || ! BlockTree::get($this->getBlocks(), $path)) {
            return;
        }

        $this->commit(BlockTree::update($this->getBlocks(), $path, function (array $block) use ($token, $value) {
            $block['data']['style'] = array_merge((array) ($block['data']['style'] ?? []), [$token => $value]);

            return $block;
        }), $path);
    }

    public function setSectionLayout(string $path, string $layout): void
    {
        $block = BlockTree::get($this->getBlocks(), $path);

        if (($block['type'] ?? null) !== SectionBlock::type() || ! isset(SectionBlock::LAYOUTS[$layout])) {
            return;
        }

        $this->commit(BlockTree::update($this->getBlocks(), $path, function (array $block) use ($layout) {
            $block['data'] = SectionBlock::applyLayout((array) $block['data'], $layout);

            return $block;
        }), $path);
    }

    public function updateBlockData(string $path, array $data): void
    {
        $block = BlockTree::get($this->getBlocks(), $path);

        if (! $block) {
            return;
        }

        $this->commit(BlockTree::update($this->getBlocks(), $path, function (array $block) use ($data) {
            $merged = array_replace((array) ($block['data'] ?? []), $data);

            if (($block['type'] ?? null) === SectionBlock::type()) {
                $merged = SectionBlock::applyLayout($merged, (string) ($merged['layout'] ?? '1-1'));
            }

            $block['data'] = $merged;

            return $block;
        }), $path);
    }

    public function switchLocale(string $locale): void
    {
        if (array_key_exists($locale, $this->documents)) {
            $this->activeLocale = $locale;
            $this->selected = null;
        }
    }

    public function setDevice(string $device): void
    {
        if (in_array($device, ['desktop', 'tablet', 'mobile'], true)) {
            $this->device = $device;
        }
    }

    public function copyLayoutFrom(string $from): void
    {
        if ($from === $this->activeLocale || ! array_key_exists($from, $this->documents)) {
            return;
        }

        $copied = $this->builder()->copyLayout($this->documents, $from, $this->activeLocale)[$this->activeLocale];

        $this->commit($copied);
        $this->selected = null;
    }

    public function save(): void
    {
        $this->builder()->save($this->getRecord(), static::$contentField, $this->documents, $this->getContentStore());
        $this->isDirty = false;

        Notification::make()
            ->success()
            ->title(__('ferga-builder::builder.canvas.saved'))
            ->send();
    }

    // ------------------------------------------------------------ history

    protected function historyKey(): string
    {
        return 'ferga-builder:history:'.$this->getId();
    }

    protected function historyCache(): Repository
    {
        return Cache::store(config('ferga-builder.history_cache_store'));
    }

    protected function getHistory(): array
    {
        return $this->historyCache()->get($this->historyKey(), ['undo' => [], 'redo' => []]);
    }

    protected function putHistory(array $history): void
    {
        $this->historyCache()->put($this->historyKey(), $history, now()->addHours(12));
    }

    protected function forgetHistory(): void
    {
        $this->historyCache()->forget($this->historyKey());
    }

    protected function snapshot(): array
    {
        return ['documents' => $this->documents, 'locale' => $this->activeLocale];
    }

    protected function pushHistory(): void
    {
        $history = $this->getHistory();
        $history['undo'][] = $this->snapshot();
        $history['undo'] = array_slice($history['undo'], -max(1, (int) config('ferga-builder.history_limit', 50)));
        $history['redo'] = [];
        $this->putHistory($history);
    }

    public function canUndo(): bool
    {
        return $this->getHistory()['undo'] !== [];
    }

    public function canRedo(): bool
    {
        return $this->getHistory()['redo'] !== [];
    }

    public function undo(): void
    {
        $history = $this->getHistory();

        if ($previous = array_pop($history['undo'])) {
            $history['redo'][] = $this->snapshot();
            $this->restore($previous);
            $this->putHistory($history);
        }
    }

    public function redo(): void
    {
        $history = $this->getHistory();

        if ($next = array_pop($history['redo'])) {
            $history['undo'][] = $this->snapshot();
            $this->restore($next);
            $this->putHistory($history);
        }
    }

    protected function restore(array $snapshot): void
    {
        $this->documents = $snapshot['documents'];
        $this->activeLocale = $snapshot['locale'];
        $this->selected = null;
        $this->isDirty = true;
    }

    // ------------------------------------------------------------ actions

    public function editBlockAction(): Action
    {
        return Action::make('editBlock')
            ->label(__('ferga-builder::builder.actions.edit'))
            ->icon(Heroicon::OutlinedPencilSquare)
            ->modalHeading(fn (array $arguments): string => $this->getBlockType(BlockTree::get($this->getBlocks(), (string) ($arguments['path'] ?? ''))['type'] ?? null)?->label() ?? '')
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalSubmitActionLabel(__('ferga-builder::builder.actions.apply'))
            ->fillForm(fn (array $arguments): array => (array) (BlockTree::get($this->getBlocks(), (string) ($arguments['path'] ?? ''))['data'] ?? []))
            ->schema(function (array $arguments): array {
                $block = BlockTree::get($this->getBlocks(), (string) ($arguments['path'] ?? ''));
                $type = $this->getBlockType($block['type'] ?? null);

                if (! $type) {
                    return [];
                }

                return [
                    Group::make($type instanceof Block ? $type->canvasSchema() : $type->schema())
                        ->extraAttributes([
                            'dir' => $this->builder()->direction($this->activeLocale),
                            'lang' => $this->builder()->htmlLang($this->activeLocale),
                        ]),
                ];
            })
            ->action(fn (array $data, array $arguments) => $this->updateBlockData((string) ($arguments['path'] ?? ''), $data));
    }

    public function copyLayoutAction(): Action
    {
        return Action::make('copyLayout')
            ->label(__('ferga-builder::builder.actions.copy_layout'))
            ->icon(Heroicon::OutlinedDocumentDuplicate)
            ->color('gray')
            ->size('sm')
            ->modalHeading(fn () => __('ferga-builder::builder.actions.copy_layout_to', ['language' => $this->getLocales()[$this->activeLocale] ?? $this->activeLocale]))
            ->modalDescription(__('ferga-builder::builder.actions.copy_layout_description'))
            ->modalSubmitActionLabel(__('ferga-builder::builder.actions.copy'))
            ->schema(fn (): array => [
                Select::make('from')
                    ->label(__('ferga-builder::builder.actions.copy_from'))
                    ->options(array_diff_key($this->getLocales(), [$this->activeLocale => true]))
                    ->default($this->activeLocale === $this->builder()->defaultLocale() ? null : $this->builder()->defaultLocale())
                    ->required(),
            ])
            ->action(fn (array $data) => $this->copyLayoutFrom((string) $data['from']));
    }
}
