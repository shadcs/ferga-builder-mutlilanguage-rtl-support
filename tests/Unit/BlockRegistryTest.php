<?php

use Ferga\FilamentBuilder\BlockRegistry;
use Ferga\FilamentBuilder\Blocks\HeadingBlock;
use Ferga\FilamentBuilder\Blocks\SectionBlock;
use Ferga\FilamentBuilder\FergaBuilder;
use Ferga\FilamentBuilder\FergaBuilderPlugin;
use Ferga\FilamentBuilder\Tests\Fixtures\Blocks\CalloutBlock;

it('registers all core blocks', function () {
    expect(array_keys(app(BlockRegistry::class)->all()))->toBe([
        'section', 'hero', 'heading', 'rich_text', 'image', 'image_text', 'gallery', 'video',
        'quote', 'button', 'call_to_action', 'highlights', 'cards', 'stats', 'testimonials',
        'faq', 'logos', 'file_download', 'divider', 'spacer',
    ]);
});

it('gives every core block a label, icon, group, view and schema', function () {
    foreach (app(BlockRegistry::class)->all() as $type => $block) {
        expect($block->label())->not->toBeEmpty()
            ->and($block->icon())->not->toBeNull()
            ->and(['layout', 'sections', 'text', 'media', 'content'])->toContain($block->group())
            ->and(view()->exists($block->view()))->toBeTrue("missing view for {$type}")
            ->and($block->schema())->toBeArray();
    }
});

it('registers, finds and forgets custom blocks', function () {
    $registry = app(BlockRegistry::class)->register(CalloutBlock::class);

    expect($registry->has('callout'))->toBeTrue()
        ->and($registry->get('callout'))->toBeInstanceOf(CalloutBlock::class);

    $registry->forget('callout');

    expect($registry->has('callout'))->toBeFalse()->and($registry->get('callout'))->toBeNull();
});

it('rejects classes that are not page blocks', function () {
    app(BlockRegistry::class)->register(stdClass::class);
})->throws(InvalidArgumentException::class);

it('keeps sections out of section columns', function () {
    expect(app(BlockRegistry::class)->nestable())->not->toHaveKey('section')->not->toHaveKey('hero')->toHaveKey('heading');
});

it('lets the panel plugin replace and extend the block list', function () {
    $plugin = FergaBuilderPlugin::make()->blocks([HeadingBlock::class, SectionBlock::class])->registerBlocks([CalloutBlock::class]);
    $plugin->register(filament()->getPanel('admin'));

    expect(array_keys(app(FergaBuilder::class)->registry()->all()))->toBe(['heading', 'section', 'callout']);
});

it('reports the files referenced by blocks', function () {
    $files = app(FergaBuilder::class)->files([
        'en' => [
            ['type' => 'image', 'data' => ['image' => 'a.jpg']],
            ['type' => 'section', 'data' => ['columns' => [['blocks' => [['type' => 'gallery', 'data' => ['images' => ['b.jpg', 'c.jpg']]]]]]]],
        ],
        'ar' => [['type' => 'file_download', 'data' => ['file' => 'd.pdf']], ['type' => 'image', 'data' => ['image' => 'a.jpg']]],
        'ku' => [['type' => 'cards', 'data' => ['items' => [['image' => 'e.jpg', 'title' => 'x'], ['title' => 'no image']]]]],
    ]);

    expect($files)->toBe(['a.jpg', 'b.jpg', 'c.jpg', 'd.pdf', 'e.jpg']);
});
