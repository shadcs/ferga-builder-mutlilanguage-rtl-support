<?php

use Ferga\FilamentBuilder\FergaBuilder;
use Illuminate\Support\Facades\Blade;

$documents = [
    'en' => [['type' => 'heading', 'data' => ['text' => 'Hello']], ['type' => 'button', 'data' => ['label' => 'Next', 'url' => '#', 'arrow' => true]]],
    'ar' => [['type' => 'heading', 'data' => ['text' => 'مرحبا']], ['type' => 'button', 'data' => ['label' => 'التالي', 'url' => '#', 'arrow' => true]]],
    'ku' => [],
    'cn' => [['type' => 'heading', 'data' => ['text' => '你好']]],
];

it('sets dir and lang per locale', function (string $locale, string $dir, string $lang, string $text) use ($documents) {
    $html = Blade::render('<x-ferga-builder::blocks :blocks="$documents" :locale="$locale" />', compact('documents', 'locale'));

    expect($html)->toContain("dir=\"{$dir}\"", "lang=\"{$lang}\"", $text);
})->with([
    ['en', 'ltr', 'en', 'Hello'],
    ['ar', 'rtl', 'ar', 'مرحبا'],
    ['cn', 'ltr', 'zh-CN', '你好'],
]);

it('uses the fallback content language for dir and lang', function () use ($documents) {
    $html = app(FergaBuilder::class)->renderer()->renderDocuments($documents, 'ku')->toHtml();

    // Kurdish is empty → English content, so the wrapper must be LTR English.
    expect($html)->toContain('dir="ltr"', 'lang="en"', 'data-fallback="true"', 'Hello')->not->toContain('dir="rtl"');
});

it('marks directional icons so they mirror in RTL', function () use ($documents) {
    $html = app(FergaBuilder::class)->renderer()->renderDocuments($documents, 'ar')->toHtml();

    expect($html)->toContain('dir="rtl"', 'fb-icon-directional', 'التالي');
});

it('does not mirror non-directional icons', function () {
    $html = app(FergaBuilder::class)->renderer()->renderDocuments(['ar' => [
        ['type' => 'highlights', 'data' => ['items' => [['icon' => 'heroicon-o-bolt', 'title' => 'سريع'], ['icon' => 'heroicon-o-arrow-right', 'title' => 'التالي']]]],
    ]], 'ar')->toHtml();

    expect(substr_count($html, 'fb-icon-directional'))->toBe(1);
});
