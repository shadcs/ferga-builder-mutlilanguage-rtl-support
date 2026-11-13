<?php

namespace Ferga\FilamentBuilder\Storage;

use Closure;
use Ferga\FilamentBuilder\Contracts\ContentStore;
use Illuminate\Database\Eloquent\Model;

/**
 * Adapter for apps that keep translations elsewhere (a `translations` table,
 * spatie/laravel-translatable, one column per locale, …).
 *
 *   CallbackStore::make(
 *       load: fn (Model $record, string $field): array => [...locale => blocks],
 *       save: fn (Model $record, string $field, array $documents) => ...,
 *   )
 */
class CallbackStore implements ContentStore
{
    public function __construct(
        protected Closure $loadUsing,
        protected Closure $saveUsing,
    ) {}

    public static function make(Closure $load, Closure $save): static
    {
        return new static($load, $save);
    }

    public function load(Model $record, string $field): array
    {
        return (array) ($this->loadUsing)($record, $field);
    }

    public function save(Model $record, string $field, array $documents): void
    {
        ($this->saveUsing)($record, $field, $documents);
    }
}
