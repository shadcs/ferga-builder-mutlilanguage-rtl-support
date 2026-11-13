<?php

use Ferga\FilamentBuilder\Blocks\FileDownloadBlock;
use Ferga\FilamentBuilder\Blocks\ImageBlock;
use Ferga\FilamentBuilder\FergaBuilder;
use Ferga\FilamentBuilder\Rendering\RenderContext;
use Ferga\FilamentBuilder\Tests\Fixtures\Models\Page;
use Illuminate\Support\Facades\Blade;

function render(array $documents, ?string $locale = 'en'): string
{
    return app(FergaBuilder::class)->renderer()->renderDocuments($documents, $locale)->toHtml();
}

it('renders every core block', function () {
    $html = render(['en' => [
        ['type' => 'heading', 'data' => ['text' => 'Welcome', 'level' => 'h1', 'eyebrow' => 'Hello', 'subtitle' => 'Sub']],
        ['type' => 'rich_text', 'data' => ['body' => '<p>Rich <strong>text</strong></p>']],
        ['type' => 'image', 'data' => ['image' => 'pics/a.jpg', 'alt' => 'Alt A', 'caption' => 'Cap']],
        ['type' => 'image_text', 'data' => ['image' => 'pics/b.jpg', 'title' => 'Side', 'body' => '<p>Body</p>', 'image_position' => 'end']],
        ['type' => 'gallery', 'data' => ['images' => ['g1.jpg', 'g2.jpg'], 'columns' => 4]],
        ['type' => 'video', 'data' => ['url' => 'https://youtu.be/dQw4w9WgXcQ', 'caption' => 'Clip']],
        ['type' => 'quote', 'data' => ['text' => 'Wise words', 'author' => 'Ana', 'role' => 'CEO']],
        ['type' => 'button', 'data' => ['label' => 'Go', 'url' => '/go', 'variant' => 'outline', 'arrow' => true, 'new_tab' => true]],
        ['type' => 'highlights', 'data' => ['title' => 'Why us', 'items' => [['icon' => 'heroicon-o-bolt', 'title' => 'Fast', 'text' => 'Very']]]],
        ['type' => 'stats', 'data' => ['items' => [['value' => '250+', 'label' => 'Lifts']]]],
        ['type' => 'file_download', 'data' => ['file' => 'docs/catalog.pdf', 'label' => 'Catalog']],
        ['type' => 'divider', 'data' => ['variant' => 'dashed']],
        ['type' => 'spacer', 'data' => ['height' => 'lg']],
        ['type' => 'section', 'data' => ['layout' => '1-2', 'columns' => [
            ['blocks' => [['type' => 'heading', 'data' => ['text' => 'Left']]]],
            ['blocks' => [['type' => 'quote', 'data' => ['text' => 'Right']]]],
        ]]],
    ]]);

    expect($html)
        ->toContain('<h1 class="fb-heading-text">Welcome</h1>', 'fb-eyebrow', 'Sub')
        ->toContain('<p>Rich <strong>text</strong></p>')
        ->toContain('src="/storage/pics/a.jpg"', 'alt="Alt A"', 'Cap')
        ->toContain('fb-media-end', 'Side')
        ->toContain('--fb-gallery-columns: 4', '/storage/g2.jpg')
        ->toContain('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->toContain('Wise words', 'Ana', 'CEO')
        ->toContain('fb-button-outline', 'href="/go"', 'target="_blank"', 'fb-icon-directional')
        ->toContain('Why us', 'Fast', '<svg')
        ->toContain('250+', 'Lifts')
        ->toContain('/storage/docs/catalog.pdf', 'Catalog', 'PDF')
        ->toContain('fb-divider-dashed', 'fb-spacer-lg')
        ->toContain('minmax(0, 1fr) minmax(0, 2fr)', 'Left', 'Right');
});

it('skips unknown block types and invalid items', function () {
    $html = render(['en' => [
        ['type' => 'does_not_exist', 'data' => ['text' => 'SECRET']],
        'garbage',
        ['type' => 'heading', 'data' => ['text' => 'Visible']],
    ]]);

    expect($html)->toContain('Visible')->not->toContain('SECRET');
});

it('escapes plain-text fields', function () {
    expect(render(['en' => [['type' => 'heading', 'data' => ['text' => '<script>alert(1)</script>']]]]))
        ->not->toContain('<script>')
        ->toContain('&lt;script&gt;');
});

it('sanitizes rich HTML strings in rich text blocks', function () {
    $unsafeBody = '<p>Safe content</p><script>alert(1)</script><img src="x" onerror="alert(1)">';
    $html = render(['en' => [
        ['type' => 'rich_text', 'data' => ['body' => $unsafeBody]],
        ['type' => 'image_text', 'data' => ['body' => $unsafeBody]],
    ]]);

    expect($html)
        ->toContain('<p>Safe content</p>')
        ->not->toContain('<script', 'onerror=');
});

it('rejects unsafe schemes in block links', function () {
    $html = render(['en' => [
        ['type' => 'button', 'data' => ['label' => 'Button', 'url' => 'JaVaScRiPt:alert(1)']],
        ['type' => 'button', 'data' => ['label' => 'Safe button', 'url' => '/safe']],
        ['type' => 'hero', 'data' => ['title' => 'Hero', 'primary_label' => 'Primary', 'primary_url' => 'javascript:alert(1)']],
        ['type' => 'call_to_action', 'data' => ['title' => 'CTA', 'secondary_label' => 'Secondary', 'secondary_url' => 'javascript:alert(1)']],
        ['type' => 'cards', 'data' => ['items' => [['title' => 'Card', 'link_label' => 'Read', 'url' => 'javascript:alert(1)']]]],
        ['type' => 'image_text', 'data' => ['button_label' => 'More', 'button_url' => 'javascript:alert(1)']],
        ['type' => 'image', 'data' => ['image' => 'safe.jpg', 'link' => 'javascript:alert(1)']],
    ]]);

    expect($html)->not->toContain('javascript:')
        ->toContain('href="#"', 'href="/safe"');
    expect(app(FergaBuilder::class)->url('data:text/html,<script>alert(1)</script>'))->toBeNull();
});

it('limits uploads to safe document and raster image types', function () {
    $fileUpload = (new FileDownloadBlock)->schema()[0];
    $fileTypes = $fileUpload->getAcceptedFileTypes();
    $imageTypes = (new ImageBlock)->schema()[0]->getAcceptedFileTypes();

    expect($fileTypes)
        ->toContain('application/pdf')
        ->not->toContain('text/html', 'image/svg+xml', 'application/javascript')
        ->and($fileUpload->shouldPreserveFilenames())->toBeFalse()
        ->and($imageTypes)
        ->toContain('image/jpeg', 'image/png')
        ->not->toContain('image/svg+xml', 'text/html');
});

it('skips empty blocks instead of rendering empty wrappers', function () {
    expect(render(['en' => [['type' => 'image', 'data' => []], ['type' => 'heading', 'data' => []]]]))
        ->not->toContain('fb-block');
});

it('applies style tokens as classes and ignores invalid ones', function () {
    $html = render(['en' => [['type' => 'heading', 'data' => ['text' => 'T', 'style' => ['spacing' => 'xl', 'background' => 'dark', 'align' => 'center', 'width' => 'evil" onload="x']]]]]);

    expect($html)->toContain('fb-space-xl', 'fb-bg-dark', 'fb-align-center', 'fb-w-normal')->not->toContain('onload');
});

it('converts YouTube and Vimeo links and rejects others', function () {
    expect(render(['en' => [['type' => 'video', 'data' => ['url' => 'https://vimeo.com/76979871']]]]))->toContain('https://player.vimeo.com/video/76979871')
        ->and(render(['en' => [['type' => 'video', 'data' => ['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10']]]]))->toContain('embed/dQw4w9WgXcQ')
        ->and(render(['en' => [['type' => 'video', 'data' => ['url' => 'https://evil.example/x']]]]))->not->toContain('iframe');
});

it('renders through the blade component from blocks, locale maps and records', function () {
    $page = Page::create(['title' => 'P', 'content' => ['en' => [['type' => 'heading', 'data' => ['text' => 'From record']]]]]);

    expect(Blade::render('<x-ferga-builder::blocks :blocks="$blocks" locale="en" class="my-page" />', ['blocks' => [['type' => 'heading', 'data' => ['text' => 'Plain list']]]]))
        ->toContain('Plain list', 'class="fb-content my-page"')
        ->and(Blade::render('<x-ferga-builder::blocks :blocks="$blocks" locale="en" />', ['blocks' => ['en' => [['type' => 'heading', 'data' => ['text' => 'Map {{ 1+1 }}']]]]]))
        ->toContain('Map {{ 1+1 }}')
        ->and(Blade::render('<x-ferga-builder::blocks :record="$page" locale="en" />', ['page' => $page]))
        ->toContain('From record');
});

it('ships prebuilt flat css without shadows', function () {
    $css = file_get_contents(__DIR__.'/../../resources/dist/ferga-builder.css').file_get_contents(__DIR__.'/../../resources/dist/ferga-builder-editor.css');

    expect($css)->not->toContain('box-shadow')
        ->toContain('--fb-primary', 'padding-inline', 'border-inline-start', '[dir="rtl"] .fb-icon-directional');

    expect(Blade::render('<x-ferga-builder::styles />'))->toContain('<style>', '.fb-content');
});

it('renders every block with empty data without errors, on the site and on the canvas', function () {
    config(['app.debug' => true]); // the renderer re-throws instead of skipping

    $renderer = app(FergaBuilder::class)->renderer();

    foreach (app(FergaBuilder::class)->registry()->all() as $type => $block) {
        foreach ([[], $block->defaults()] as $data) {
            foreach ([false, true] as $editing) {
                $context = new RenderContext('en', editing: $editing, path: '0', editor: ['selected' => null, 'chromeDir' => 'ltr']);
                expect($renderer->renderBlock(['id' => 'x', 'type' => $type, 'data' => $data], $context))->toBeString();
            }
        }
    }
});
