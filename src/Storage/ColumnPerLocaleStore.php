<?php

namespace Ferga\FilamentBuilder\Storage;

use Ferga\FilamentBuilder\Contracts\ContentStore;
use Ferga\FilamentBuilder\FergaBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * One column per locale: content_en, content_ar, … (pattern configurable).
 */
class ColumnPerLocaleStore implements ContentStore
{
    public function __construct(protected string $pattern = '{field}_{locale}') {}

    public function load(Model $record, string $field): array
    {
        $documents = [];

        foreach (app(FergaBuilder::class)->localeCodes() as $locale) {
            $raw = $record->getAttribute($this->column($field, $locale));
            $raw = is_string($raw) ? json_decode($raw, true) : $raw;

            if (is_array($raw) && $raw !== []) {
                $documents[$locale] = $raw;
            }
        }

        return app(FergaBuilder::class)->normalizeDocuments($documents);
    }

    public function save(Model $record, string $field, array $documents): void
    {
        foreach (app(FergaBuilder::class)->localeCodes() as $locale) {
            $column = $this->column($field, $locale);
            $blocks = $documents[$locale] ?? [];
            $value = $blocks === [] ? null : $blocks;

            $record->setAttribute($column, $record->hasCast($column) || $value === null ? $value : json_encode($value, JSON_UNESCAPED_UNICODE));
        }

        if ($record->exists && $record->isDirty()) {
            $record->save();
        }
    }

    protected function column(string $field, string $locale): string
    {
        return str_replace(['{field}', '{locale}'], [$field, strtolower($locale)], $this->pattern);
    }
}
