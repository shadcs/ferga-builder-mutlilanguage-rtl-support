<?php

namespace Ferga\FilamentBuilder\Support;

use Illuminate\Support\Str;

/**
 * Pure operations on a block document (list of blocks, sections nest blocks in
 * columns). Addresses:
 *
 *  - block path:     "3"      → 4th top-level block
 *                    "3.1.0"  → 1st block in column 2 of section "3"
 *  - container path: ""       → the top level
 *                    "3.1"    → column 2 of section "3"
 */
class BlockTree
{
    public static function newId(): string
    {
        return strtolower((string) Str::ulid());
    }

    /** Re-index lists, drop invalid items, add missing ids. */
    public static function clean(array $blocks): array
    {
        $out = [];

        foreach (array_values($blocks) as $block) {
            if (! is_array($block) || ! is_string($block['type'] ?? null)) {
                continue;
            }

            $block['id'] = is_string($block['id'] ?? null) && $block['id'] !== '' ? $block['id'] : static::newId();
            $block['data'] = is_array($block['data'] ?? null) ? $block['data'] : [];

            if (isset($block['data']['columns']) && is_array($block['data']['columns'])) {
                $block['data']['columns'] = array_map(
                    fn ($column) => ['blocks' => static::clean((array) (is_array($column) ? ($column['blocks'] ?? []) : []))] + (is_array($column) ? $column : []),
                    array_values($block['data']['columns']),
                );
            }

            $out[] = ['id' => $block['id'], 'type' => $block['type'], 'data' => $block['data']];
        }

        return $out;
    }

    /** Copy of the blocks with brand-new ids (used for copy-layout/duplicate). */
    public static function refreshIds(array $blocks): array
    {
        return array_map(function ($block) {
            $block['id'] = static::newId();

            if (isset($block['data']['columns']) && is_array($block['data']['columns'])) {
                foreach ($block['data']['columns'] as $c => $column) {
                    $block['data']['columns'][$c]['blocks'] = static::refreshIds((array) ($column['blocks'] ?? []));
                }
            }

            return $block;
        }, static::clean($blocks));
    }

    /** Depth-first walk over every block (callback receives block and path). */
    public static function walk(array $blocks, callable $callback, string $prefix = ''): void
    {
        foreach (array_values($blocks) as $i => $block) {
            if (! is_array($block)) {
                continue;
            }

            $path = $prefix === '' ? (string) $i : "{$prefix}.{$i}";
            $callback($block, $path);

            foreach ((array) ($block['data']['columns'] ?? []) as $c => $column) {
                static::walk((array) ($column['blocks'] ?? []), $callback, "{$path}.{$c}");
            }
        }
    }

    public static function count(array $blocks): int
    {
        $count = 0;
        static::walk($blocks, function () use (&$count) {
            $count++;
        });

        return $count;
    }

    /** @return array{0: string, 1: int} [container path, index] */
    public static function split(string $path): array
    {
        $segments = explode('.', $path);
        $index = (int) array_pop($segments);

        return [implode('.', $segments), $index];
    }

    /** Array key path (for data_get/Arr) of a container's list. */
    protected static function containerKey(string $container): ?string
    {
        if ($container === '') {
            return null;
        }

        $segments = explode('.', $container);

        if (count($segments) % 2 !== 0) {
            throw new \InvalidArgumentException("Invalid container path [{$container}].");
        }

        $key = [];

        foreach (array_chunk($segments, 2) as [$block, $column]) {
            $key[] = "{$block}.data.columns.{$column}.blocks";
        }

        return implode('.', $key);
    }

    public static function getContainer(array $blocks, string $container): ?array
    {
        $key = static::containerKey($container);

        if ($key === null) {
            return $blocks;
        }

        $list = data_get($blocks, $key);

        return is_array($list) ? array_values($list) : null;
    }

    protected static function setContainer(array $blocks, string $container, array $list): array
    {
        $key = static::containerKey($container);

        if ($key === null) {
            return array_values($list);
        }

        data_set($blocks, $key, array_values($list));

        return $blocks;
    }

    public static function get(array $blocks, string $path): ?array
    {
        [$container, $index] = static::split($path);

        return static::getContainer($blocks, $container)[$index] ?? null;
    }

    public static function update(array $blocks, string $path, callable $callback): array
    {
        [$container, $index] = static::split($path);
        $list = static::getContainer($blocks, $container);

        if (! isset($list[$index])) {
            return $blocks;
        }

        $list[$index] = $callback($list[$index]);

        return static::setContainer($blocks, $container, $list);
    }

    public static function insert(array $blocks, string $container, int $index, array $block): array
    {
        $list = static::getContainer($blocks, $container);

        if ($list === null) {
            return $blocks;
        }

        $index = max(0, min($index, count($list)));
        array_splice($list, $index, 0, [$block]);

        return static::setContainer($blocks, $container, $list);
    }

    /** @return array{0: array, 1: ?array} [blocks, removed block] */
    public static function remove(array $blocks, string $path): array
    {
        [$container, $index] = static::split($path);
        $list = static::getContainer($blocks, $container);

        if (! isset($list[$index])) {
            return [$blocks, null];
        }

        [$removed] = array_splice($list, $index, 1);

        return [static::setContainer($blocks, $container, $list), $removed];
    }

    public static function duplicate(array $blocks, string $path): array
    {
        $block = static::get($blocks, $path);

        if (! $block) {
            return $blocks;
        }

        [$container, $index] = static::split($path);

        return static::insert($blocks, $container, $index + 1, static::refreshIds([$block])[0]);
    }

    /**
     * Move a block to `$index` within `$container` (index measured before the
     * move, like a drop slot). Sections cannot be dropped inside sections and
     * a block cannot be moved into itself.
     */
    public static function move(array $blocks, string $path, string $container, int $index, ?callable $canNest = null): array
    {
        $block = static::get($blocks, $path);

        if (! $block) {
            return $blocks;
        }

        if ($container !== '' && ($container === $path || str_starts_with($container.'.', $path.'.'))) {
            return $blocks; // into itself
        }

        if ($container !== '' && $canNest && ! $canNest($block)) {
            return $blocks;
        }

        [$fromContainer, $fromIndex] = static::split($path);

        if ($fromContainer === $container && ($index === $fromIndex || $index === $fromIndex + 1)) {
            return $blocks; // no-op
        }

        [$blocks] = static::remove($blocks, $path);

        if ($fromContainer === $container && $fromIndex < $index) {
            $index--;
        }

        // Removing a top-level block shifts later sections' indices.
        if ($fromContainer === '' && $container !== '') {
            $segments = explode('.', $container);

            if ((int) $segments[0] > $fromIndex) {
                $segments[0] = (string) ((int) $segments[0] - 1);
                $container = implode('.', $segments);
            }
        }

        return static::insert($blocks, $container, $index, $block);
    }
}
