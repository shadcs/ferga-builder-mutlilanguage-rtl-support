<?php

use Ferga\FilamentBuilder\Tests\Fixtures\Models\Article;
use Ferga\FilamentBuilder\Tests\Fixtures\Models\Page;
use Ferga\FilamentBuilder\Tests\Fixtures\Models\User;
use Ferga\FilamentBuilder\Tests\Fixtures\Resources\ArticleCanvas;
use Ferga\FilamentBuilder\Tests\Fixtures\Resources\PageResource\Pages\PageCanvas;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::create(['name' => 'Admin', 'email' => 'a@example.test', 'password' => 'x']));

    $this->page = Page::create(['title' => 'Home', 'content' => [
        'en' => [
            ['type' => 'heading', 'data' => ['text' => 'Hello']],
            ['type' => 'section', 'data' => ['layout' => '1-1', 'columns' => [['blocks' => []], ['blocks' => []]]]],
        ],
    ]]);
});

function canvas(Page $page)
{
    return Livewire::test(PageCanvas::class, ['record' => $page->getRouteKey()]);
}

function types(array $blocks): array
{
    return array_column($blocks, 'type');
}

it('renders the canvas over HTTP inside a real panel', function () {
    $this->get("/admin/pages/{$this->page->id}/builder")
        ->assertOk()
        ->assertSee('Visual editor')
        ->assertSee('Hello')
        ->assertSee('Drop blocks here');
});

it('loads every locale and renders the real blocks', function () {
    canvas($this->page)
        ->assertSet('activeLocale', 'en')
        ->assertSet('documents.ar', [])
        ->assertSee('Hello')
        ->assertSee('Outline')
        ->assertSeeHtml('class="fb-content" dir="ltr" lang="en"');
});

it('inserts, moves, styles, duplicates and removes blocks', function () {
    $c = canvas($this->page)
        ->call('insertBlock', 'quote', '1.0', 0)
        ->call('insertBlock', 'divider', '', 0);

    $en = $c->get('documents.en');
    expect(types($en))->toBe(['divider', 'heading', 'section'])
        ->and($en[2]['data']['columns'][0]['blocks'][0]['type'])->toBe('quote')
        ->and($c->get('selected'))->toBe('0')
        ->and($c->get('isDirty'))->toBeTrue();

    // Sections cannot be dropped into columns.
    $c->call('moveBlock', '2', '2.1', 0);
    expect(types($c->get('documents.en')))->toBe(['divider', 'heading', 'section']);

    // Move the heading into the second column.
    $c->call('moveBlock', '1', '2.1', 0);
    $en = $c->get('documents.en');
    expect(types($en))->toBe(['divider', 'section'])
        ->and($en[1]['data']['columns'][1]['blocks'][0]['data']['text'])->toBe('Hello');

    $c->call('setStyle', '0', 'background', 'dark')
        ->call('setStyle', '0', 'background', 'neon'); // invalid: ignored
    expect($c->get('documents.en.0.data.style'))->toBe(['background' => 'dark']);

    $c->call('duplicateBlock', '0')->call('removeBlock', '1');
    expect(types($c->get('documents.en')))->toBe(['divider', 'section']);

    $c->call('setSectionLayout', '1', '1-1-1');
    expect($c->get('documents.en.1.data.columns'))->toHaveCount(3);

    $c->call('setSectionLayout', '1', '1');
    $columns = $c->get('documents.en.1.data.columns');
    expect($columns)->toHaveCount(1)
        ->and(array_column(array_column($columns[0]['blocks'], 'data'), 'text'))->toContain('Hello');

    $c->call('insertBlock', 'unknown-type');
    expect(types($c->get('documents.en')))->toBe(['divider', 'section']);
});

it('inserts after the selected block on click and lifts sections to the top level', function () {
    $c = canvas($this->page)
        ->call('insertBlock', 'quote', '1.0', 0)
        ->call('selectBlock', '1.0.0')
        ->call('insertBlock', 'button')
        ->call('insertBlock', 'section');

    $en = $c->get('documents.en');

    expect(types($en[1]['data']['columns'][0]['blocks']))->toBe(['quote', 'button'])
        ->and(types($en))->toBe(['heading', 'section', 'section']);
});

it('undoes and redoes', function () {
    $c = canvas($this->page)
        ->call('insertBlock', 'quote', '', 0)
        ->call('insertBlock', 'divider', '', 0);

    $c->call('undo');
    expect(types($c->get('documents.en')))->toBe(['quote', 'heading', 'section']);

    $c->call('undo');
    expect(types($c->get('documents.en')))->toBe(['heading', 'section']);

    $c->call('redo');
    expect(types($c->get('documents.en')))->toBe(['quote', 'heading', 'section']);

    $c->call('insertBlock', 'spacer');
    $c->call('redo'); // redo stack cleared by new change
    expect(types($c->get('documents.en')))->toBe(['quote', 'heading', 'section', 'spacer']);
});

it('edits block content through the Filament modal form', function () {
    canvas($this->page)
        ->callAction(TestAction::make('editBlock')->arguments(['path' => '0']), ['text' => 'Edited', 'level' => 'h1'])
        ->assertHasNoActionErrors()
        ->assertSet('documents.en.0.data.text', 'Edited')
        ->assertSet('documents.en.0.data.level', 'h1')
        ->assertSee('Edited');
});

it('validates block forms', function () {
    canvas($this->page)
        ->callAction(TestAction::make('editBlock')->arguments(['path' => '0']), ['text' => ''])
        ->assertHasActionErrors(['text' => 'required']);
});

it('switches locales, renders RTL and copies a layout', function () {
    $c = canvas($this->page)
        ->call('switchLocale', 'ar')
        ->assertSet('activeLocale', 'ar')
        ->assertSee('has no blocks yet')
        ->assertSeeHtml('class="fb-content" dir="rtl" lang="ar"');

    $c->callAction('copyLayout', ['from' => 'en'])->assertHasNoActionErrors();

    $ar = $c->get('documents.ar');
    expect(types($ar))->toBe(['heading', 'section'])
        ->and($ar[0]['id'])->not->toBe($c->get('documents.en.0.id'));

    $c->callAction(TestAction::make('editBlock')->arguments(['path' => '0']), ['text' => 'مرحبا', 'level' => 'h2'])
        ->assertSet('documents.en.0.data.text', 'Hello')
        ->assertSet('documents.ar.0.data.text', 'مرحبا');

    $c->call('switchLocale', 'xx')->assertSet('activeLocale', 'ar');
});

it('saves all locales', function () {
    canvas($this->page)
        ->call('insertBlock', 'quote', '1.1', 0)
        ->call('switchLocale', 'ku')
        ->call('copyLayoutFrom', 'en')
        ->call('save')
        ->assertSet('isDirty', false)
        ->assertNotified();

    $content = $this->page->fresh()->content;

    expect(array_keys($content))->toBe(['en', 'ku'])
        ->and($content['en'][1]['data']['columns'][1]['blocks'][0]['type'])->toBe('quote')
        ->and($content['ku'][1]['data']['columns'][1]['blocks'][0]['type'])->toBe('quote');
});

it('saves through a custom store', function () {
    $article = Article::create(['title' => 'A']);

    Livewire::test(ArticleCanvas::class, ['record' => $article->getRouteKey()])
        ->call('switchLocale', 'ar')
        ->call('insertBlock', 'heading')
        ->call('save');

    expect(json_decode(DB::table('translations')->where(['foreign_key' => $article->id, 'locale' => 'ar'])->value('value'), true)[0]['type'])
        ->toBe('heading');
});

it('shows the editor chrome in the admin language', function () {
    app()->setLocale('ar');

    canvas($this->page)
        ->assertSee('المحرر المرئي')
        ->assertSee('حفظ')
        ->assertSeeHtml('dir="rtl"');
});
