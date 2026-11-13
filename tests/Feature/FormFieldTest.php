<?php

use Ferga\FilamentBuilder\Tests\Fixtures\Models\Article;
use Ferga\FilamentBuilder\Tests\Fixtures\Models\Page;
use Ferga\FilamentBuilder\Tests\Fixtures\Models\User;
use Ferga\FilamentBuilder\Tests\Fixtures\Resources\ArticleEdit;
use Ferga\FilamentBuilder\Tests\Fixtures\Resources\PageResource;
use Ferga\FilamentBuilder\Tests\Fixtures\Resources\PageResource\Pages\CreatePage;
use Ferga\FilamentBuilder\Tests\Fixtures\Resources\PageResource\Pages\EditPage;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::create(['name' => 'Admin', 'email' => 'a@example.test', 'password' => 'x']));
});

it('loads every locale into the form with locale tabs and RTL wrappers', function () {
    $page = Page::create(['title' => 'Home', 'content' => [
        'en' => [['type' => 'heading', 'data' => ['text' => 'Hello']]],
        'ar' => [['type' => 'heading', 'data' => ['text' => 'مرحبا']]],
    ]]);

    $component = Livewire::test(EditPage::class, ['record' => $page->getRouteKey()]);

    $state = $component->get('data.content');

    expect(array_keys($state))->toBe(['en', 'ar', 'ku', 'cn'])
        ->and(array_values($state['ar'])[0]['data']['text'])->toBe('مرحبا')
        ->and($state['ku'])->toBe([]);

    $component->assertSee('العربية')
        ->assertSee('کوردی')
        ->assertSee('中文')
        ->assertSeeHtml('dir="rtl"')
        ->assertSeeHtml('lang="ckb"')
        ->assertSee('visitors see the English version');
});

it('saves the per-locale content through the store', function () {
    $page = Page::create(['title' => 'Home', 'content' => ['en' => [['type' => 'heading', 'data' => ['text' => 'Hello']]]]]);

    $component = Livewire::test(EditPage::class, ['record' => $page->getRouteKey()]);
    $en = $component->get('data.content.en');
    $key = array_key_first($en);

    $component
        ->set("data.content.en.{$key}.data.text", 'Hello world')
        ->set('data.content.ar', [(string) str()->uuid() => ['type' => 'heading', 'data' => ['text' => 'مرحبا بالعالم', 'level' => 'h2']]])
        ->call('save')
        ->assertHasNoFormErrors();

    $content = $page->fresh()->content;

    expect(array_keys($content))->toBe(['en', 'ar'])
        ->and($content['en'][0]['data']['text'])->toBe('Hello world')
        ->and($content['ar'][0]['data']['text'])->toBe('مرحبا بالعالم')
        ->and(array_is_list($content['ar']))->toBeTrue();
});

it('creates records with builder content', function () {
    Livewire::test(CreatePage::class)
        ->fillForm(['title' => 'New'])
        ->set('data.content.en', [(string) str()->uuid() => ['type' => 'quote', 'data' => ['text' => 'Hi there']]])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Page::where('title', 'New')->first()->content['en'][0]['data']['text'])->toBe('Hi there');
});

it('copies the layout from another language in the form', function () {
    $page = Page::create(['title' => 'Home', 'content' => ['en' => [
        ['type' => 'heading', 'data' => ['text' => 'One']],
        ['type' => 'quote', 'data' => ['text' => 'Two']],
    ]]]);

    $component = Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
        ->callAction(TestAction::make('fergaBuilderCopyLayoutAr')->schemaComponent('content.ferga-builder-tabs.ferga-builder-locale-ar.ferga-builder-copy-ar', schema: 'form'), ['from' => 'en'])
        ->assertHasNoActionErrors();

    $ar = array_values($component->get('data.content.ar'));

    expect($ar)->toHaveCount(2)
        ->and($ar[1]['type'])->toBe('quote');

    $component->call('save');

    expect($page->fresh()->content['ar'][1]['data']['text'])->toBe('Two');
});

it('works with a custom store (translations table)', function () {
    $article = Article::create(['title' => 'A']);
    $article->setBuilderBlocks('en', [['type' => 'heading', 'data' => ['text' => 'EN']]], 'body');

    $component = Livewire::test(ArticleEdit::class, ['record' => $article->getRouteKey()]);

    expect(array_values($component->get('data.body.en'))[0]['data']['text'])->toBe('EN');

    $component
        ->set('data.body.ku', [(string) str()->uuid() => ['type' => 'heading', 'data' => ['text' => 'سڵاو', 'level' => 'h2']]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(json_decode(DB::table('translations')->where(['foreign_key' => $article->id, 'locale' => 'ku'])->value('value'), true)[0]['data']['text'])
        ->toBe('سڵاو');
});

it('links to the visual canvas', function () {
    $page = Page::create(['title' => 'Home']);

    Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertActionHasUrl('openFergaBuilderCanvas', url("/admin/pages/{$page->id}/builder"));
});

it('keeps languages the field does not edit', function () {
    $page = Page::create(['title' => 'Home', 'content' => [
        'en' => [['type' => 'heading', 'data' => ['text' => 'EN']]],
        'cn' => [['type' => 'heading', 'data' => ['text' => '中文']]],
    ]]);

    PageResource::$limitLocales = ['en', 'ar'];

    try {
        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->assertDontSee('中文')
            ->set('data.content.ar', [(string) str()->uuid() => ['type' => 'heading', 'data' => ['text' => 'AR', 'level' => 'h2']]])
            ->call('save')
            ->assertHasNoFormErrors();
    } finally {
        PageResource::$limitLocales = null;
    }

    expect(array_keys($page->fresh()->content))->toBe(['en', 'cn', 'ar'])
        ->and($page->fresh()->content['cn'][0]['data']['text'])->toBe('中文');
});
