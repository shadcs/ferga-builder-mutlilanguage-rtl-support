<?php

use Ferga\FilamentBuilder\Support\BlockTree;

function tree(): array
{
    return BlockTree::clean([
        ['type' => 'heading', 'data' => ['text' => 'A']],
        ['type' => 'section', 'data' => ['layout' => '1-1', 'columns' => [
            ['blocks' => [['type' => 'heading', 'data' => ['text' => 'B']]]],
            ['blocks' => []],
        ]]],
        ['type' => 'heading', 'data' => ['text' => 'C']],
    ]);
}

function texts(array $blocks): array
{
    $out = [];
    BlockTree::walk($blocks, function ($block, $path) use (&$out) {
        $out[$path] = $block['data']['text'] ?? $block['type'];
    });

    return $out;
}

it('cleans blocks and assigns ids', function () {
    $blocks = BlockTree::clean([['type' => 'heading'], 'junk', ['data' => []]]);

    expect($blocks)->toHaveCount(1)
        ->and($blocks[0]['id'])->toBeString()->not->toBeEmpty()
        ->and($blocks[0]['data'])->toBe([]);
});

it('gets, inserts, removes and duplicates', function () {
    $blocks = tree();

    expect(BlockTree::get($blocks, '1.0.0')['data']['text'])->toBe('B');

    $blocks = BlockTree::insert($blocks, '1.1', 0, ['id' => 'x', 'type' => 'heading', 'data' => ['text' => 'X']]);
    expect(texts($blocks))->toMatchArray(['1.1.0' => 'X']);

    [$blocks, $removed] = BlockTree::remove($blocks, '0');
    expect($removed['data']['text'])->toBe('A')->and(texts($blocks)['0'])->toBe('section');

    $blocks = BlockTree::duplicate($blocks, '0.0.0');
    expect(texts($blocks))->toMatchArray(['0.0.0' => 'B', '0.0.1' => 'B'])
        ->and(BlockTree::get($blocks, '0.0.0')['id'])->not->toBe(BlockTree::get($blocks, '0.0.1')['id']);
});

it('moves blocks within and between containers', function () {
    // Top-level block into a later section: the section index shifts.
    $blocks = BlockTree::move(tree(), '0', '1.1', 0);
    expect(texts($blocks))->toBe(['0' => 'section', '0.0.0' => 'B', '0.1.0' => 'A', '1' => 'C']);

    // Down within the same list (drop-slot index measured before removal).
    $blocks = BlockTree::move(tree(), '0', '', 3);
    expect(array_values(texts($blocks)))->toBe(['section', 'B', 'C', 'A']);

    // Out of a column to the top level.
    $blocks = BlockTree::move(tree(), '1.0.0', '', 0);
    expect(texts($blocks))->toBe(['0' => 'B', '1' => 'A', '2' => 'section', '3' => 'C']);
});

it('refuses impossible moves', function () {
    $blocks = tree();

    expect(BlockTree::move($blocks, '1', '1.0', 0))->toBe($blocks) // into itself
        ->and(BlockTree::move($blocks, '1', '1.0', 0, fn () => false))->toBe($blocks)
        ->and(BlockTree::move($blocks, '0', '', 1))->toBe($blocks); // no-op
});

it('refreshes ids recursively', function () {
    $blocks = tree();
    $copy = BlockTree::refreshIds($blocks);

    expect($copy[1]['data']['columns'][0]['blocks'][0]['id'])->not->toBe($blocks[1]['data']['columns'][0]['blocks'][0]['id'])
        ->and(texts($copy))->toBe(texts($blocks));
});
