<?php

namespace Ferga\FilamentBuilder\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Loads and saves the per-locale block documents of a record.
 *
 * A "document" is a list of blocks. A store works with all locales at once:
 * ['en' => [...blocks], 'ar' => [...blocks]]. Locales without blocks may be
 * omitted (or empty) — renderers fall back to the default locale.
 *
 * Ship your own implementation to plug Ferga-Builder into an existing
 * translation table (see README "Custom storage").
 */
interface ContentStore
{
    /** @return array<string, array<int, array>> */
    public function load(Model $record, string $field): array;

    /** @param  array<string, array<int, array>>  $documents */
    public function save(Model $record, string $field, array $documents): void;
}
