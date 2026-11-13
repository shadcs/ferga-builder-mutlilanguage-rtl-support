<?php

namespace Ferga\FilamentBuilder\Rendering;

use Ferga\FilamentBuilder\FergaBuilder;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Throwable;

class BlockRenderer
{
    public function __construct(protected FergaBuilder $builder) {}

    /**
     * Render a locale map (with fallback) or a plain list of blocks,
     * wrapped in <div class="fb-content" dir lang>.
     */
    public function renderDocuments(array $documents, ?string $locale = null, ?bool $fallback = null, array $attributes = []): Htmlable
    {
        $resolved = $this->builder->resolve($documents, $locale ?? app()->getLocale(), $fallback);

        return $this->wrap($this->renderList($resolved['blocks'], new RenderContext($resolved['locale'])), $resolved['locale'], $attributes + [
            'data-fallback' => $resolved['fallback'] ? 'true' : null,
        ]);
    }

    /** Render a list of blocks (no wrapper). Unknown block types are skipped. */
    public function renderList(array $blocks, RenderContext $context, string $prefix = ''): Htmlable
    {
        $html = '';

        foreach (array_values($blocks) as $i => $block) {
            $html .= $this->renderBlock($block, $context->at($prefix === '' ? (string) $i : "{$prefix}.{$i}"));
        }

        return new HtmlString($html);
    }

    public function renderBlock(mixed $block, RenderContext $context): string
    {
        if (! is_array($block)) {
            return '';
        }

        $type = $this->builder->registry()->get($block['type'] ?? null);

        if (! $type) {
            return '';
        }

        try {
            $html = (string) (($rendered = $type->render($block, $context)) instanceof Htmlable ? $rendered->toHtml() : $rendered);
        } catch (Throwable $e) {
            if ($context->editing || config('app.debug')) {
                throw $e;
            }

            report($e);

            return '';
        }

        return $html;
    }

    public function wrap(Htmlable|string $inner, string $locale, array $attributes = []): Htmlable
    {
        $attrs = [
            'class' => trim('fb-content '.($attributes['class'] ?? '')),
            'dir' => $this->builder->direction($locale),
            'lang' => $this->builder->htmlLang($locale),
            'data-locale' => $locale,
        ] + $attributes;

        $html = '';

        foreach ($attrs as $name => $value) {
            if ($value === null || $value === false) {
                continue;
            }

            $html .= ' '.e($name).'="'.e($value).'"';
        }

        $inner = $inner instanceof Htmlable ? $inner->toHtml() : $inner;

        return new HtmlString("<div{$html}>{$inner}</div>");
    }
}
