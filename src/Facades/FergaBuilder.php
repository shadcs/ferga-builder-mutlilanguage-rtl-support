<?php

namespace Ferga\FilamentBuilder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array locales()
 * @method static string defaultLocale()
 * @method static bool isRtl(?string $locale)
 * @method static string direction(?string $locale)
 * @method static array resolve(array $documents, ?string $locale = null, ?bool $fallback = null)
 * @method static array copyLayout(array $documents, string $from, string $to)
 * @method static array blocksFor(\Illuminate\Database\Eloquent\Model $record, string $field = 'content', ?string $locale = null)
 * @method static \Ferga\FilamentBuilder\BlockRegistry registry()
 *
 * @see \Ferga\FilamentBuilder\FergaBuilder
 */
class FergaBuilder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Ferga\FilamentBuilder\FergaBuilder::class;
    }
}
