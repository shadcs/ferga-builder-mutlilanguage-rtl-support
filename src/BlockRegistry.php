<?php

namespace Ferga\FilamentBuilder;

use Ferga\FilamentBuilder\Contracts\PageBlock;
use InvalidArgumentException;

class BlockRegistry
{
    /** @var array<string, PageBlock> */
    protected array $blocks = [];

    /** @param  class-string<PageBlock>|PageBlock  $block */
    public function register(string|PageBlock $block): static
    {
        if (is_string($block)) {
            if (! is_subclass_of($block, PageBlock::class)) {
                throw new InvalidArgumentException("[{$block}] must implement ".PageBlock::class.'.');
            }

            $block = app($block);
        }

        $this->blocks[$block::type()] = $block;

        return $this;
    }

    /** @param  array<int, class-string<PageBlock>|PageBlock>  $blocks */
    public function registerMany(array $blocks): static
    {
        foreach ($blocks as $block) {
            $this->register($block);
        }

        return $this;
    }

    /** Replace every registered block. */
    public function set(array $blocks): static
    {
        $this->blocks = [];

        return $this->registerMany($blocks);
    }

    public function forget(string $type): static
    {
        unset($this->blocks[$type]);

        return $this;
    }

    public function has(?string $type): bool
    {
        return $type !== null && isset($this->blocks[$type]);
    }

    public function get(?string $type): ?PageBlock
    {
        return $type === null ? null : ($this->blocks[$type] ?? null);
    }

    /** @return array<string, PageBlock> */
    public function all(): array
    {
        return $this->blocks;
    }

    /** Blocks allowed inside a section column. @return array<string, PageBlock> */
    public function nestable(): array
    {
        return array_filter($this->blocks, fn (PageBlock $block) => $block->nestable());
    }

    /** @return array<string, array<string, PageBlock>> group => blocks */
    public function grouped(?bool $nestable = null): array
    {
        $groups = [];

        foreach ($this->blocks as $type => $block) {
            if ($nestable === true && ! $block->nestable()) {
                continue;
            }

            $groups[$block->group()][$type] = $block;
        }

        return $groups;
    }
}
