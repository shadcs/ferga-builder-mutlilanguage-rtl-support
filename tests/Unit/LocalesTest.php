<?php

use Ferga\FilamentBuilder\FergaBuilder;

beforeEach(fn () => $this->builder = app(FergaBuilder::class));

it('reads locales from the plugin', function () {
    expect($this->builder->locales())->toBe(['en' => 'English', 'ar' => 'العربية', 'ku' => 'کوردی', 'cn' => '中文'])
        ->and($this->builder->defaultLocale())->toBe('en');
});

it('detects right-to-left locales', function (string $locale, bool $rtl) {
    expect($this->builder->isRtl($locale))->toBe($rtl)
        ->and($this->builder->direction($locale))->toBe($rtl ? 'rtl' : 'ltr');
})->with([
    ['ar', true], ['ar_IQ', true], ['ar-SA', true], ['ku', true], ['ckb', true], ['fa', true],
    ['he', true], ['ur', true], ['en', false], ['cn', false], ['zh_CN', false], ['kmr', false],
]);

it('allows a custom RTL list', function () {
    $this->builder->setRtlLocales(['ar']);

    expect($this->builder->isRtl('ku'))->toBeFalse()->and($this->builder->isRtl('ar'))->toBeTrue();
});

it('maps app locale codes to html lang tags', function () {
    expect($this->builder->htmlLang('cn'))->toBe('zh-CN')
        ->and($this->builder->htmlLang('ku'))->toBe('ckb')
        ->and($this->builder->htmlLang('zh_CN'))->toBe('zh-CN')
        ->and($this->builder->htmlLang('ar'))->toBe('ar');
});

it('falls back to the default locale when a locale has no blocks', function () {
    $documents = ['en' => [['type' => 'heading', 'data' => ['text' => 'Hi']]], 'ar' => []];

    expect($this->builder->resolve($documents, 'ar'))->toMatchArray(['locale' => 'en', 'fallback' => true])
        ->and($this->builder->resolve($documents, 'en'))->toMatchArray(['locale' => 'en', 'fallback' => false])
        ->and($this->builder->resolve($documents, 'ar', fallback: false))->toMatchArray(['blocks' => [], 'locale' => 'ar']);
});

it('falls back to any locale with content when the default is empty too', function () {
    $documents = ['en' => [], 'ku' => [['type' => 'heading', 'data' => ['text' => 'سڵاو']]]];

    expect($this->builder->resolve($documents, 'ar')['locale'])->toBe('ku');
});

it('can disable fallback globally', function () {
    $this->builder->setFallback(false);

    expect($this->builder->resolve(['en' => [['type' => 'divider']]], 'ar')['blocks'])->toBe([]);
});

it('treats legacy plain lists as default-locale content', function () {
    $documents = $this->builder->normalizeDocuments(json_encode([['type' => 'heading', 'data' => ['text' => 'Old']]]));

    expect(array_keys($documents))->toBe(['en'])->and($documents['en'][0]['data']['text'])->toBe('Old');
});

it('copies a layout between locales with fresh ids', function () {
    $documents = ['en' => [['id' => 'a', 'type' => 'heading', 'data' => ['text' => 'Hi']]], 'ar' => []];
    $copied = $this->builder->copyLayout($documents, 'en', 'ar');

    expect($copied['ar'][0]['data'])->toBe(['text' => 'Hi'])
        ->and($copied['ar'][0]['id'])->not->toBe('a')
        ->and($copied['en'])->toBe($documents['en']);
});
