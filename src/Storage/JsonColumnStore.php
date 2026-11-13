<?php

namespace Ferga\FilamentBuilder\Storage;

use Ferga\FilamentBuilder\Contracts\ContentStore;
use Ferga\FilamentBuilder\FergaBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * Stores all locales in one JSON column: {"en": [...], "ar": [...]}.
 * A column holding a plain list of blocks is read as default-locale content.
 */
class JsonColumnStore implements ContentStore
{
    public function load(Model $record, string $field): array
    {
        return app(FergaBuilder::class)->normalizeDocuments($record->getAttribute($field));
    }

    public function save(Model $record, string $field, array $documents): void
    {
        $documents = array_filter($documents, fn ($blocks) => ! empty($blocks));
        $value = $documents === [] ? null : $documents;

        $record->setAttribute($field, $record->hasCast($field) || $value === null
            ? $value
            : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if ($record->exists && $record->isDirty($field)) {
            $record->save();
        }
    }
}
