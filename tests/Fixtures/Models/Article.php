<?php

namespace Ferga\FilamentBuilder\Tests\Fixtures\Models;

use Ferga\FilamentBuilder\Concerns\HasBuilderContent;
use Ferga\FilamentBuilder\Contracts\ContentStore;
use Ferga\FilamentBuilder\Contracts\HasBuilderContentStore;
use Ferga\FilamentBuilder\Storage\CallbackStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Keeps content in a Voyager-style `translations` table — exercises the
 * pluggable storage adapter.
 */
class Article extends Model implements HasBuilderContentStore
{
    use HasBuilderContent;

    protected $guarded = [];

    public function builderContentStore(string $field): ?ContentStore
    {
        return CallbackStore::make(
            load: fn (Model $record, string $field) => DB::table('translations')
                ->where(['table_name' => 'articles', 'column_name' => $field, 'foreign_key' => $record->getKey()])
                ->pluck('value', 'locale')
                ->map(fn ($json) => json_decode($json, true) ?: [])
                ->all(),
            save: function (Model $record, string $field, array $documents) {
                foreach ($documents as $locale => $blocks) {
                    DB::table('translations')->updateOrInsert(
                        ['table_name' => 'articles', 'column_name' => $field, 'foreign_key' => $record->getKey(), 'locale' => $locale],
                        ['value' => json_encode($blocks)],
                    );
                }
            },
        );
    }
}
