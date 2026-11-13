<?php

use Ferga\FilamentBuilder\FergaBuilder;
use Ferga\FilamentBuilder\FergaBuilderPlugin;
use Ferga\FilamentBuilder\Forms\Components\PageBuilder;
use Ferga\FilamentBuilder\Support\BlockTree;
use Ferga\FilamentBuilder\Templates\Template;
use Ferga\FilamentBuilder\Templates\TemplateCopy;
use Ferga\FilamentBuilder\Tests\Fixtures\Models\Page;
use Ferga\FilamentBuilder\Tests\Fixtures\Models\User;
use Ferga\FilamentBuilder\Tests\Fixtures\Resources\PageResource\Pages\EditPage;
use Ferga\FilamentBuilder\Tests\Fixtures\Resources\PageResource\Pages\PageCanvas;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

it('ships section and page templates', function () {
    $templates = app(FergaBuilder::class)->templates();

    expect(array_keys($templates->sections()))->toBe([
        'hero_centered', 'hero_split', 'hero_cover', 'features', 'about_story', 'services',
        'testimonials', 'faq', 'logos', 'call_to_action', 'contact',
    ])->and(array_keys($templates->pages()))->toBe(['landing_page', 'product_page', 'about_page', 'services_page']);
});

it('builds every template with registered blocks, labels and descriptions', function (string $locale) {
    config(['app.debug' => true]);
    $builder = app(FergaBuilder::class);

    foreach ($builder->templates()->all() as $key => $template) {
        $blocks = $template->build($locale);

        expect($blocks)->not->toBeEmpty()
            ->and(trans()->has("ferga-builder::templates.names.{$key}", $locale))->toBeTrue()
            ->and($template->getDescription())->not->toBeNull();

        BlockTree::walk($blocks, fn ($block) => expect($builder->registry()->has($block['type']))->toBeTrue());

        // Renders without errors and without untranslated keys.
        $html = $builder->renderer()->renderDocuments([$locale => $blocks], $locale)->toHtml();
        expect($html)->not->toContain('ferga-builder::');
    }
})->with(['en', 'ar', 'ku', 'cn']);

it('writes sample text in the language being edited', function () {
    $hero = fn (string $locale) => app(FergaBuilder::class)->templates()->get('hero_centered')->build($locale)[0]['data']['title'];

    expect($hero('en'))->toBe('Quality you can rely on')
        ->and($hero('ar'))->toBe('جودة يمكنك الاعتماد عليها')
        ->and($hero('ckb'))->toBe($hero('ku'))
        ->and($hero('cn'))->toBe('值得信赖的品质')
        ->and($hero('fr'))->toBe('Quality you can rely on');
});

it('gives every build fresh ids', function () {
    $template = app(FergaBuilder::class)->templates()->get('contact');

    expect($template->build('en')[0]['id'])->not->toBe($template->build('en')[0]['id']);
});

it('drops block types that are not registered', function () {
    app(FergaBuilder::class)->registry()->forget('faq');

    expect(app(FergaBuilder::class)->templates()->get('landing_page')->blockTypes('en'))->not->toContain('faq');
});

it('lets the plugin add, replace and disable templates', function () {
    $custom = Template::make('pricing')->label('Pricing')->blocks(fn (TemplateCopy $t) => [['type' => 'heading', 'data' => ['text' => 'Plans']]]);

    FergaBuilderPlugin::make()->registerTemplates([$custom])->register(filament()->getPanel('admin'));
    expect(app(FergaBuilder::class)->templates()->get('pricing')->getLabel())->toBe('Pricing');

    FergaBuilderPlugin::make()->templates([$custom])->register(filament()->getPanel('admin'));
    expect(array_keys(app(FergaBuilder::class)->templates()->all()))->toBe(['pricing']);

    FergaBuilderPlugin::make()->templates(false)->register(filament()->getPanel('admin'));
    expect(app(FergaBuilder::class)->templates()->all())->toBe([]);
});

describe('editors', function () {
    beforeEach(function () {
        $this->actingAs(User::create(['name' => 'Admin', 'email' => 'a@example.test', 'password' => 'x']));
        $this->page = Page::create(['title' => 'Home', 'content' => ['en' => [
            ['type' => 'heading', 'data' => ['text' => 'Hello']],
            ['type' => 'section', 'data' => ['layout' => '1-1', 'columns' => [['blocks' => []], ['blocks' => []]]]],
        ]]]);
    });

    it('inserts templates on the canvas', function () {
        $c = Livewire::test(PageCanvas::class, ['record' => $this->page->getRouteKey()])
            ->assertSee('Templates')
            ->assertSee('Landing page')
            ->call('insertTemplate', 'faq'); // nothing selected: at the end

        expect(array_column($c->get('documents.en'), 'type'))->toBe(['heading', 'section', 'faq'])
            ->and($c->get('selected'))->toBe('2');

        // Selected block → after it; dropped into a column → after that section.
        $c->call('selectBlock', '0')->call('insertTemplate', 'call_to_action');
        $c->call('insertTemplate', 'logos', '2.1', 0);
        expect(array_column($c->get('documents.en'), 'type'))->toBe(['heading', 'call_to_action', 'section', 'logos', 'faq']);

        $c->call('switchLocale', 'ar')->call('insertTemplate', 'landing_page');
        $ar = $c->get('documents.ar');
        expect($ar[0]['data']['title'])->toBe('جودة يمكنك الاعتماد عليها')
            ->and(count($ar))->toBeGreaterThan(5);

        $c->call('undo');
        expect($c->get('documents.ar'))->toBe([]);

        $c->call('insertTemplate', 'nope');
        expect($c->get('documents.ar'))->toBe([]);

        $c->call('redo')->call('save');
        expect($this->page->fresh()->content['ar'][0]['type'])->toBe('hero');
    });

    it('inserts templates from the form field', function () {
        $component = Livewire::test(EditPage::class, ['record' => $this->page->getRouteKey()])
            ->callAction(
                TestAction::make('fergaBuilderInsertTemplateKu')->schemaComponent('content.ferga-builder-tabs.ferga-builder-locale-ku.ferga-builder-copy-ku', schema: 'form'),
                ['template' => 'contact'],
            )
            ->assertHasNoActionErrors()
            ->call('save')
            ->assertHasNoFormErrors();

        $ku = $this->page->fresh()->content['ku'];

        expect($ku[0]['type'])->toBe('section')
            ->and($ku[0]['data']['columns'][0]['blocks'][0]['data']['text'])->toBe('پەیوەندیمان پێوە بکە')
            ->and(array_is_list($ku[0]['data']['columns']))->toBeTrue();
    });

    it('can hide templates on a form field', function () {
        $field = PageBuilder::make('content')->templates(false);

        expect((fn () => $this->evaluate($this->hasTemplates))->call($field))->toBeFalse();
    });
});
