<?php

namespace Ferga\FilamentBuilder\View\Components;

use Ferga\FilamentBuilder\FergaBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

/**
 * <x-ferga-builder::blocks :blocks="$page->content" :locale="app()->getLocale()" />
 * <x-ferga-builder::blocks :record="$page" field="content" />
 *
 * `blocks` may be a locale map ({"en": [...], "ar": [...]}) or a plain list.
 */
class Blocks extends Component
{
    public function __construct(
        public mixed $blocks = null,
        public ?string $locale = null,
        public ?Model $record = null,
        public string $field = 'content',
        public ?bool $fallback = null,
    ) {}

    public function render(): \Closure
    {
        return function (array $data) {
            $builder = app(FergaBuilder::class);
            $locale = $this->locale ?? app()->getLocale();

            if ($this->record) {
                $documents = $builder->load($this->record, $this->field);
            } else {
                $raw = is_string($this->blocks) ? json_decode($this->blocks, true) : $this->blocks;
                $raw = is_array($raw) ? $raw : [];
                $documents = $builder->isLocaleMap($raw) ? $builder->normalizeDocuments($raw) : [$locale => $raw];
            }

            $attributes = $data['attributes']->getAttributes();

            // Return a view (not a string): Laravel would compile a returned string as Blade.
            return view('ferga-builder::components.raw', [
                'html' => $builder->renderer()->renderDocuments($documents, $locale, $this->fallback, $attributes)->toHtml(),
            ]);
        };
    }
}
