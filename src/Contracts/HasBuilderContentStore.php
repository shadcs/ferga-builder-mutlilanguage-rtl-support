<?php

namespace Ferga\FilamentBuilder\Contracts;

/**
 * Implement on a model to choose the ContentStore per field
 * (e.g. an adapter for an existing `translations` table).
 */
interface HasBuilderContentStore
{
    public function builderContentStore(string $field): ?ContentStore;
}
