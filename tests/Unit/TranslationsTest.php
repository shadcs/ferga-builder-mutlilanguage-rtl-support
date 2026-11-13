<?php

use Illuminate\Support\Arr;

function keysOf(string $locale): array
{
    return array_keys(Arr::dot(require __DIR__."/../../resources/lang/{$locale}/builder.php"));
}

it('translates the editor UI into every shipped language', function (string $locale) {
    expect(keysOf($locale))->toEqualCanonicalizing(keysOf('en'));
})->with(['ar', 'ku', 'ckb', 'zh_CN', 'cn', 'zh']);

it('serves the right language and falls back to English', function () {
    app()->setLocale('ar');
    expect(__('ferga-builder::builder.actions.save'))->toBe('حفظ');

    app()->setLocale('ku');
    expect(__('ferga-builder::builder.actions.save'))->toBe('پاشەکەوتکردن');

    app()->setLocale('cn');
    expect(__('ferga-builder::builder.actions.save'))->toBe('保存');

    app()->setLocale('fr');
    expect(__('ferga-builder::builder.actions.save'))->toBe('Save');
});
