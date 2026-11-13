<?php

use Ferga\FilamentBuilder\FergaBuilder;
use Ferga\FilamentBuilder\Storage\CallbackStore;
use Ferga\FilamentBuilder\Storage\JsonColumnStore;
use Ferga\FilamentBuilder\Tests\Fixtures\Models\Article;
use Ferga\FilamentBuilder\Tests\Fixtures\Models\Page;
use Illuminate\Support\Facades\DB;

it('stores all locales in one json column', function () {
    $page = Page::create(['title' => 'P']);
    app(FergaBuilder::class)->save($page, 'content', [
        'en' => [['type' => 'heading', 'data' => ['text' => 'Hi']]],
        'ar' => [['type' => 'heading', 'data' => ['text' => 'مرحبا']]],
        'ku' => [],
    ]);

    $raw = json_decode(DB::table('pages')->value('content'), true);

    expect(array_keys($raw))->toBe(['en', 'ar'])
        ->and($raw['ar'][0]['data']['text'])->toBe('مرحبا')
        ->and($raw['en'][0]['id'])->toBeString()
        ->and(app(FergaBuilder::class)->storeFor($page, 'content'))->toBeInstanceOf(JsonColumnStore::class);

    expect($page->fresh()->builderBlocks('content', 'ar')[0]['data']['text'])->toBe('مرحبا')
        ->and($page->fresh()->builderBlocks('content', 'ku')[0]['data']['text'])->toBe('Hi');
});

it('plugs in a custom store for apps with translation tables', function () {
    $article = Article::create(['title' => 'A']);

    expect(app(FergaBuilder::class)->storeFor($article, 'body'))->toBeInstanceOf(CallbackStore::class);

    $article->setBuilderBlocks('en', [['type' => 'heading', 'data' => ['text' => 'EN']]], 'body');
    $article->setBuilderBlocks('ar', [['type' => 'heading', 'data' => ['text' => 'AR']]], 'body');

    expect(DB::table('translations')->where('foreign_key', $article->id)->count())->toBe(2)
        ->and($article->builderBlocks('body', 'ar')[0]['data']['text'])->toBe('AR')
        ->and($article->builderBlocks('body', 'cn')[0]['data']['text'])->toBe('EN')
        ->and($article->renderBuilder('body', 'ar')->toHtml())->toContain('dir="rtl"', 'AR');
});
