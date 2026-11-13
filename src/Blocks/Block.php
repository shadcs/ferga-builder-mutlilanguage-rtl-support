<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Ferga\FilamentBuilder\Contracts\PageBlock;
use Ferga\FilamentBuilder\FergaBuilder;
use Ferga\FilamentBuilder\Rendering\RenderContext;
use Ferga\FilamentBuilder\Support\StyleTokens;
use Filament\Forms\Components\Builder\Block as FilamentBlock;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Base class for block types. Extend it and implement type(), schema() and,
 * usually, a Blade view at ferga-builder::blocks.{type} (or override view()).
 */
abstract class Block implements PageBlock
{
    public static function make(): static
    {
        return app(static::class);
    }

    abstract public static function type(): string;

    abstract public function schema(): array;

    public function label(): string
    {
        $key = 'ferga-builder::builder.blocks.'.static::type();
        $label = __($key);

        return $label === $key ? Str::headline(static::type()) : $label;
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedSquare2Stack;
    }

    public function group(): string
    {
        return 'content';
    }

    public function view(): string
    {
        return 'ferga-builder::blocks.'.str_replace('_', '-', static::type());
    }

    public function fileFields(): array
    {
        return [];
    }

    public function defaults(): array
    {
        return [];
    }

    public function preview(array $data): ?string
    {
        return null;
    }

    public function nestable(): bool
    {
        return true;
    }

    /** Schema used by the canvas "edit block" modal (defaults to schema()). */
    public function canvasSchema(): array
    {
        return $this->schema();
    }

    public function viewData(array $data, RenderContext $context): array
    {
        return [];
    }

    public function render(array $block, RenderContext $context): Htmlable|string
    {
        $data = is_array($block['data'] ?? null) ? $block['data'] : [];
        $style = StyleTokens::normalize($data['style'] ?? null);

        $inner = view($this->view(), [
            'block' => $block,
            'data' => $data,
            'type' => $this,
            'style' => $style,
            'context' => $context,
            'locale' => $context->locale,
            'isRtl' => $context->isRtl(),
            'builder' => app(FergaBuilder::class),
        ] + $this->viewData($data, $context))->render();

        if (trim($inner) === '') {
            return '';
        }

        $classes = implode(' ', ['fb-block', 'fb-block-'.str_replace('_', '-', static::type()), ...StyleTokens::classes($style)]);
        $anchor = filled($data['anchor'] ?? null) ? ' id="'.e(Str::slug($data['anchor'])).'"' : '';

        return new HtmlString("<div class=\"{$classes}\" data-block=\"".e(static::type())."\"{$anchor}><div class=\"fb-block-inner\">{$inner}</div></div>");
    }

    /** Filament Builder block for the form field editor. */
    public function toFilamentBlock(): FilamentBlock
    {
        return FilamentBlock::make(static::type())
            ->label(function (?array $state) {
                $preview = $state === null ? null : $this->preview($state);

                return filled($preview) ? $this->label().': '.$preview : $this->label();
            })
            ->icon($this->icon())
            ->schema([
                ...$this->schema(),
                static::styleSection(),
            ]);
    }

    public static function styleSection(): Section
    {
        $fields = [];

        foreach (array_keys(StyleTokens::definitions()) as $token) {
            $fields[] = Select::make("style.{$token}")
                ->label(__("ferga-builder::builder.style.{$token}"))
                ->options(StyleTokens::options($token))
                ->default(StyleTokens::definitions()[$token]['default'])
                ->selectablePlaceholder(false);
        }

        return Section::make(__('ferga-builder::builder.style.title'))
            ->schema($fields)
            ->columns(['default' => 2, 'lg' => 4])
            ->collapsible()
            ->collapsed()
            ->compact();
    }

    // ------------------------------------------------------------ helpers

    protected static function t(string $key): string
    {
        return __('ferga-builder::builder.'.$key);
    }

    protected function upload(string $name): FileUpload
    {
        $builder = app(FergaBuilder::class);

        return FileUpload::make($name)
            ->label(static::t('fields.'.$name))
            ->disk($builder->disk())
            ->directory(config('ferga-builder.upload_directory', 'ferga-builder'))
            ->visibility('public');
    }

    protected function image(string $name): FileUpload
    {
        return $this->upload($name)->image()->imageEditor()->maxSize(10240);
    }

    /** Plain-text snippet from HTML, rich-editor JSON or text. */
    public static function excerpt(mixed $text, int $limit = 50): ?string
    {
        if (is_array($text)) {
            $parts = [];
            array_walk_recursive($text, function ($value, $key) use (&$parts) {
                if ($key === 'text' && is_string($value)) {
                    $parts[] = $value;
                }
            });
            $text = implode(' ', $parts);
        }

        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $text))));

        return $text === '' ? null : Str::limit($text, $limit);
    }
}
