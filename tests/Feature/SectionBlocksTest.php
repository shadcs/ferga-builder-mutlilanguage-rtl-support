<?php

use Ferga\FilamentBuilder\FergaBuilder;

function renderSections(array $blocks, string $locale = 'en'): string
{
    return app(FergaBuilder::class)->renderer()->renderDocuments([$locale => $blocks], $locale)->toHtml();
}

it('renders the hero in its three layouts', function () {
    $html = renderSections([
        ['type' => 'hero', 'data' => ['layout' => 'center', 'eyebrow' => 'Hi', 'title' => 'Centered hero', 'text' => 'Intro', 'primary_label' => 'Start', 'primary_url' => '/start', 'secondary_label' => 'More', 'secondary_url' => '/more']],
        ['type' => 'hero', 'data' => ['layout' => 'split', 'title' => 'Split hero', 'image' => 'hero.jpg', 'alt' => 'Lift']],
        ['type' => 'hero', 'data' => ['layout' => 'cover', 'title' => 'Cover hero', 'image' => 'cover.jpg']],
    ]);

    expect($html)
        ->toContain('fb-hero-center', '<h1 class="fb-hero-title">Centered hero</h1>', 'href="/start"', 'fb-button-outline', 'href="/more"')
        ->toContain('fb-hero-split', 'src="/storage/hero.jpg"', 'alt="Lift"')
        ->toContain('fb-hero-cover', "--fb-hero-image: url('/storage/cover.jpg')");
});

it('renders call to action, faq, cards, testimonials and logos', function () {
    $html = renderSections([
        ['type' => 'call_to_action', 'data' => ['title' => 'Ready?', 'text' => 'Call now', 'primary_label' => 'Contact', 'primary_url' => '/contact', 'style' => ['background' => 'primary']]],
        ['type' => 'faq', 'data' => ['title' => 'FAQ', 'open_first' => true, 'items' => [['question' => 'Q1?', 'answer' => "A1\nline"], ['question' => 'Q2?', 'answer' => 'A2']]]],
        ['type' => 'cards', 'data' => ['columns' => 2, 'items' => [['title' => 'Card A', 'text' => 'Text A', 'link_label' => 'Read', 'url' => '/a', 'image' => 'a.jpg'], ['title' => 'Card B']]]],
        ['type' => 'testimonials', 'data' => ['items' => [['quote' => 'Great work', 'name' => 'Sara', 'role' => 'CEO']]]],
        ['type' => 'logos', 'data' => ['title' => 'Trusted by', 'images' => ['l1.png', 'l2.png']]],
    ]);

    expect($html)
        ->toContain('fb-cta', 'Ready?', 'fb-bg-primary', 'href="/contact"')
        ->toContain('<details class="fb-faq-item"  open >', 'Q1?', 'A1<br />', 'Q2?')
        ->toContain('--fb-grid-columns: 2', 'Card A', 'src="/storage/a.jpg"', 'href="/a"', 'Card B')
        ->toContain('Great work', 'Sara', 'CEO', 'fb-avatar-initial')
        ->toContain('fb-logos-grayscale', '/storage/l2.png');

    expect(substr_count($html, '<details'))->toBe(2)->and(substr_count($html, ' open '))->toBe(1);
});

it('mirrors section blocks in RTL', function () {
    $html = renderSections([
        ['type' => 'hero', 'data' => ['title' => 'مرحبا', 'primary_label' => 'ابدأ', 'primary_url' => '#']],
        ['type' => 'cards', 'data' => ['items' => [['title' => 'بطاقة', 'link_label' => 'المزيد', 'url' => '#']]]],
    ], 'ar');

    expect($html)->toContain('dir="rtl"')->and(substr_count($html, 'fb-icon-directional'))->toBe(2);
});
