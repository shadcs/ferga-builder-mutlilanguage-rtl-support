<?php

namespace Ferga\FilamentBuilder\Forms\Components;

use Closure;
use Ferga\FilamentBuilder\Contracts\ContentStore;
use Ferga\FilamentBuilder\Contracts\PageBlock;
use Ferga\FilamentBuilder\FergaBuilder;
use Ferga\FilamentBuilder\Support\BlockTree;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Multilingual block editor field.
 *
 *   PageBuilder::make('content')
 *
 * State: ['en' => [blocks], 'ar' => [blocks], …]. On a model form the field
 * loads/saves through the record's ContentStore (JSON column by default, or a
 * custom adapter), so the column itself never needs to be fillable.
 */
class PageBuilder extends Field
{
    protected string $view = 'ferga-builder::forms.page-builder';

    /** @var array<string, string>|Closure|null */
    protected array|Closure|null $locales = null;

    protected ContentStore|Closure|null $store = null;

    /** @var array<int, string>|Closure|null */
    protected array|Closure|null $blockTypes = null;

    protected bool|Closure $canCopyLayout = true;

    protected bool|Closure $hasTemplates = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);
        $this->columnSpanFull();

        $this->schema(fn (PageBuilder $component): array => [$component->getLocaleTabs()]);

        $this->loadStateFromRelationshipsUsing(static function (PageBuilder $component, Model $record): void {
            $documents = app(FergaBuilder::class)->load($record, $component->getName(), $component->getStore($record));
            $state = [];

            foreach (array_keys($component->getLocales()) as $locale) {
                $state[$locale] = $documents[$locale] ?? [];
            }

            $component->state($state);
        });

        $this->saveRelationshipsUsing(static function (PageBuilder $component, Model $record): void {
            $builder = app(FergaBuilder::class);
            $store = $component->getStore($record);

            // Keep languages this field doesn't edit (e.g. ->locales(['en', 'ar'])).
            $documents = array_replace($builder->load($record, $component->getName(), $store), $component->getDocuments());

            $builder->save($record, $component->getName(), $documents, $store);
        });

        // On model forms the store saves the content; elsewhere (settings pages,
        // custom actions) the locale map is dehydrated like a normal field.
        $this->dehydrated(static fn (PageBuilder $component): bool => blank($component->getModel()));
    }

    /** @param  array<string, string>|array<int, string>|Closure  $locales */
    public function locales(array|Closure|null $locales): static
    {
        $this->locales = $locales;

        return $this;
    }

    /** @return array<string, string> */
    public function getLocales(): array
    {
        $builder = app(FergaBuilder::class);
        $locales = $this->evaluate($this->locales);

        if (blank($locales)) {
            return $builder->locales();
        }

        $out = [];

        foreach ($locales as $code => $label) {
            is_int($code) ? $out[$label] = $builder->localeLabel($label) : $out[$code] = $label;
        }

        return $out;
    }

    public function store(ContentStore|Closure|null $store): static
    {
        $this->store = $store;

        return $this;
    }

    public function getStore(?Model $record = null): ContentStore
    {
        $record ??= $this->getRecord();

        return $this->evaluate($this->store, ['record' => $record])
            ?? app(FergaBuilder::class)->storeFor($record, $this->getName());
    }

    /** Restrict the available block types. @param  array<int, string>|Closure  $types */
    public function blocks(array|Closure|null $types): static
    {
        $this->blockTypes = $types;

        return $this;
    }

    /** @return array<string, PageBlock> */
    public function getBlockTypes(): array
    {
        $all = app(FergaBuilder::class)->registry()->all();
        $only = $this->evaluate($this->blockTypes);

        return blank($only) ? $all : array_intersect_key($all, array_flip($only));
    }

    public function copyLayout(bool|Closure $condition = true): static
    {
        $this->canCopyLayout = $condition;

        return $this;
    }

    public function templates(bool|Closure $condition = true): static
    {
        $this->hasTemplates = $condition;

        return $this;
    }

    /** Clean locale map from the current (Livewire) state. */
    public function getDocuments(): array
    {
        $snapshot = $this->getChildSchema()->getStateSnapshot();
        $documents = [];

        foreach (array_keys($this->getLocales()) as $locale) {
            $documents[$locale] = BlockTree::clean(array_values((array) ($snapshot[$locale] ?? [])));
        }

        return $documents;
    }

    public function getLocaleTabs(): Tabs
    {
        $builder = app(FergaBuilder::class);
        $locales = $this->getLocales();
        $default = array_key_exists($builder->defaultLocale(), $locales) ? $builder->defaultLocale() : array_key_first($locales);
        $tabs = [];

        foreach ($locales as $locale => $label) {
            $tabs[] = Tab::make($label)
                ->key('ferga-builder-locale-'.$locale)
                ->badge(fn (): ?int => count((array) ($this->getRawState()[$locale] ?? [])) ?: null)
                ->extraAttributes(['data-locale' => $locale])
                ->schema($this->getLocaleSchema($locale, $default, $locales));
        }

        return Tabs::make(__('ferga-builder::builder.form.languages'))
            ->key('ferga-builder-tabs')
            ->tabs($tabs)
            ->contained(false)
            ->persistTabInQueryString(false);
    }

    protected function getLocaleSchema(string $locale, string $default, array $locales): array
    {
        $builder = app(FergaBuilder::class);
        $others = array_diff_key($locales, [$locale => true]);
        $schema = [];

        if ($locale !== $default) {
            $schema[] = Text::make(fn () => __('ferga-builder::builder.form.fallback_notice', ['language' => $locales[$default]]))
                ->visible(fn (): bool => empty($this->getRawState()[$locale] ?? []))
                ->color('gray');
        }

        $actions = [];

        if ($this->evaluate($this->hasTemplates) && ($templateOptions = $builder->templates()->options()) !== []) {
            $actions[] = Action::make('fergaBuilderInsertTemplate'.Str::studly($locale))
                ->label(__('ferga-builder::builder.actions.insert_template'))
                ->icon(Heroicon::OutlinedSquare3Stack3d)
                ->color('gray')
                ->size('sm')
                ->modalHeading(__('ferga-builder::builder.actions.insert_template'))
                ->modalDescription(__('ferga-builder::builder.canvas.templates_hint'))
                ->modalSubmitActionLabel(__('ferga-builder::builder.actions.insert'))
                ->schema([
                    Select::make('template')
                        ->label(__('ferga-builder::builder.actions.template'))
                        ->options($templateOptions)
                        ->searchable()
                        ->required(),
                ])
                ->action(fn (array $data) => $this->insertTemplateInState((string) $data['template'], $locale));
        }

        if ($others !== [] && $this->evaluate($this->canCopyLayout)) {
            $actions[] = Action::make('fergaBuilderCopyLayout'.Str::studly($locale))
                ->label(__('ferga-builder::builder.actions.copy_layout'))
                ->icon(Heroicon::OutlinedDocumentDuplicate)
                ->color('gray')
                ->size('sm')
                ->modalHeading(__('ferga-builder::builder.actions.copy_layout_to', ['language' => $locales[$locale]]))
                ->modalDescription(__('ferga-builder::builder.actions.copy_layout_description'))
                ->modalSubmitActionLabel(__('ferga-builder::builder.actions.copy'))
                ->schema([
                    Select::make('from')
                        ->label(__('ferga-builder::builder.actions.copy_from'))
                        ->options($others)
                        ->default(array_key_exists($default, $others) ? $default : array_key_first($others))
                        ->selectablePlaceholder(false)
                        ->required(),
                ])
                ->action(fn (array $data) => $this->copyLayoutInState((string) $data['from'], $locale));
        }

        if ($actions !== []) {
            $schema[] = Actions::make($actions)->alignEnd()->key('ferga-builder-copy-'.$locale);
        }

        // Only the content is in the locale's direction; notices and actions
        // stay in the admin language's direction.
        $schema[] = Group::make([$this->getLocaleBuilder($locale)])
            ->extraAttributes([
                'dir' => $builder->direction($locale),
                'lang' => $builder->htmlLang($locale),
                'class' => 'fb-form-locale',
            ]);

        return $schema;
    }

    protected function getLocaleBuilder(string $locale): Builder
    {
        $builder = app(FergaBuilder::class);

        return Builder::make($locale)
            ->hiddenLabel()
            ->blocks(array_map(fn (PageBlock $block) => method_exists($block, 'toFilamentBlock')
                ? $block->toFilamentBlock()
                : Block::make($block::type())->label($block->label())->icon($block->icon())->schema($block->schema()), array_values($this->getBlockTypes())))
            ->addActionLabel(__('ferga-builder::builder.actions.add_block'))
            ->blockPickerColumns(['default' => 2, 'lg' => 3])
            ->blockNumbers(false)
            ->collapsible()
            ->cloneable()
            ->reorderableWithButtons()
            ->extraAttributes(['data-direction' => $builder->direction($locale)]);
    }

    /** Append a template's blocks (in that locale's language) to a locale. */
    public function insertTemplateInState(string $key, string $locale): void
    {
        $template = app(FergaBuilder::class)->templates()->get($key);

        if (! $template) {
            return;
        }

        $state = (array) $this->getRawState();
        $items = (array) ($state[$locale] ?? []);

        foreach ($template->build($locale) as $block) {
            $items[(string) Str::uuid()] = static::toFormState($block);
        }

        $state[$locale] = $items;
        $this->rawState($state);
    }

    /**
     * Shape plain block data like Filament's hydrated state: lists of items
     * (repeaters, nested builders) are keyed by UUID.
     */
    public static function toFormState(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $isItemList = array_is_list($value) && $value !== [] && count(array_filter($value, 'is_array')) === count($value);
        $out = [];

        foreach ($value as $key => $item) {
            $out[$isItemList ? (string) Str::uuid() : $key] = static::toFormState($item);
        }

        return $out;
    }

    /** Copy the raw (hydrated) state of one locale into another. */
    public function copyLayoutInState(string $from, string $to): void
    {
        $state = (array) $this->getRawState();
        $items = [];

        foreach ((array) ($state[$from] ?? []) as $item) {
            if (is_array($item)) {
                $item['id'] = BlockTree::newId();
            }

            $items[(string) Str::uuid()] = $item;
        }

        $state[$to] = $items;
        $this->rawState($state);
    }
}
