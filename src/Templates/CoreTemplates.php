<?php

namespace Ferga\FilamentBuilder\Templates;

use Filament\Support\Icons\Heroicon;

/**
 * Built-in section and page templates. Sample text comes from
 * resources/lang/{locale}/templates.php ("copy"), so a template inserted
 * while editing Arabic starts with Arabic text.
 */
class CoreTemplates
{
    /** @return array<int, Template> */
    public static function all(): array
    {
        return [...static::sections(), ...static::pages()];
    }

    /** @return array<int, Template> */
    public static function sections(): array
    {
        return [
            Template::make('hero_centered')->icon(Heroicon::OutlinedRectangleGroup)->blocks(fn (TemplateCopy $t) => [static::heroCentered($t)]),
            Template::make('hero_split')->icon(Heroicon::OutlinedViewColumns)->blocks(fn (TemplateCopy $t) => [static::heroSplit($t)]),
            Template::make('hero_cover')->icon(Heroicon::OutlinedPhoto)->blocks(fn (TemplateCopy $t) => [static::heroCover($t)]),
            Template::make('features')->icon(Heroicon::OutlinedSparkles)->blocks(fn (TemplateCopy $t) => static::features($t)),
            Template::make('about_story')->icon(Heroicon::OutlinedNewspaper)->blocks(fn (TemplateCopy $t) => static::aboutStory($t)),
            Template::make('services')->icon(Heroicon::OutlinedRectangleStack)->blocks(fn (TemplateCopy $t) => [static::services($t)]),
            Template::make('testimonials')->icon(Heroicon::OutlinedChatBubbleLeftRight)->blocks(fn (TemplateCopy $t) => [static::testimonials($t)]),
            Template::make('faq')->icon(Heroicon::OutlinedQuestionMarkCircle)->blocks(fn (TemplateCopy $t) => [static::faq($t)]),
            Template::make('logos')->icon(Heroicon::OutlinedBuildingOffice2)->blocks(fn (TemplateCopy $t) => [static::logos($t)]),
            Template::make('call_to_action')->icon(Heroicon::OutlinedMegaphone)->blocks(fn (TemplateCopy $t) => [static::callToAction($t)]),
            Template::make('contact')->icon(Heroicon::OutlinedPhone)->blocks(fn (TemplateCopy $t) => [static::contact($t)]),
        ];
    }

    /** @return array<int, Template> */
    public static function pages(): array
    {
        return [
            Template::make('landing_page')->category(Template::PAGE)->blocks(fn (TemplateCopy $t) => [
                static::heroCentered($t),
                static::logos($t),
                ...static::features($t),
                ...static::aboutStory($t),
                static::testimonials($t),
                static::faq($t),
                static::callToAction($t),
            ]),
            Template::make('product_page')->category(Template::PAGE)->blocks(fn (TemplateCopy $t) => [
                static::heroSplit($t),
                ...static::features($t),
                ['type' => 'gallery', 'data' => ['columns' => 3, 'images' => []]],
                static::stats($t, 'muted'),
                static::faq($t),
                static::callToAction($t),
            ]),
            Template::make('about_page')->category(Template::PAGE)->blocks(fn (TemplateCopy $t) => [
                static::heroCover($t),
                ...static::aboutStory($t),
                static::services($t),
                static::testimonials($t),
                static::callToAction($t),
            ]),
            Template::make('services_page')->category(Template::PAGE)->blocks(fn (TemplateCopy $t) => [
                static::heroCentered($t),
                static::services($t),
                ...static::features($t),
                static::faq($t),
                static::contact($t),
            ]),
        ];
    }

    // ------------------------------------------------------------ sections

    protected static function heroCentered(TemplateCopy $t): array
    {
        return ['type' => 'hero', 'data' => [
            'layout' => 'center',
            'eyebrow' => $t('hero_eyebrow'),
            'title' => $t('hero_title'),
            'text' => $t('hero_text'),
            'primary_label' => $t('get_started'),
            'primary_url' => '#',
            'secondary_label' => $t('learn_more'),
            'secondary_url' => '#',
            'style' => ['spacing' => 'xl'],
        ]];
    }

    protected static function heroSplit(TemplateCopy $t): array
    {
        return ['type' => 'hero', 'data' => [
            'layout' => 'split',
            'eyebrow' => $t('hero_eyebrow'),
            'title' => $t('hero_title'),
            'text' => $t('hero_text'),
            'primary_label' => $t('get_started'),
            'primary_url' => '#',
            'secondary_label' => $t('learn_more'),
            'secondary_url' => '#',
            'style' => ['spacing' => 'lg'],
        ]];
    }

    protected static function heroCover(TemplateCopy $t): array
    {
        return ['type' => 'hero', 'data' => [
            'layout' => 'cover',
            'eyebrow' => $t('hero_eyebrow'),
            'title' => $t('hero_title'),
            'text' => $t('hero_text'),
            'primary_label' => $t('get_started'),
            'primary_url' => '#',
            'style' => ['spacing' => 'md', 'width' => 'wide'],
        ]];
    }

    protected static function features(TemplateCopy $t): array
    {
        return [
            ['type' => 'heading', 'data' => [
                'eyebrow' => $t('features_eyebrow'),
                'text' => $t('features_title'),
                'subtitle' => $t('features_text'),
                'level' => 'h2',
                'style' => ['align' => 'center', 'spacing' => 'md'],
            ]],
            ['type' => 'highlights', 'data' => [
                'columns' => 3,
                'items' => [
                    ['icon' => 'heroicon-o-bolt', 'title' => $t('feature_1_title'), 'text' => $t('feature_1_text')],
                    ['icon' => 'heroicon-o-shield-check', 'title' => $t('feature_2_title'), 'text' => $t('feature_2_text')],
                    ['icon' => 'heroicon-o-chat-bubble-left-right', 'title' => $t('feature_3_title'), 'text' => $t('feature_3_text')],
                ],
                'style' => ['spacing' => 'md'],
            ]],
        ];
    }

    protected static function aboutStory(TemplateCopy $t): array
    {
        return [
            ['type' => 'image_text', 'data' => [
                'title' => $t('story_title'),
                'body' => '<p>'.e($t('story_text')).'</p>',
                'image_position' => 'start',
                'button_label' => $t('learn_more'),
                'button_url' => '#',
                'style' => ['spacing' => 'lg'],
            ]],
            static::stats($t, 'none'),
        ];
    }

    protected static function stats(TemplateCopy $t, string $background): array
    {
        return ['type' => 'stats', 'data' => [
            'items' => [
                ['value' => '250+', 'label' => $t('stat_1')],
                ['value' => '15', 'label' => $t('stat_2')],
                ['value' => '24/7', 'label' => $t('stat_3')],
            ],
            'style' => ['background' => $background, 'spacing' => 'lg'],
        ]];
    }

    protected static function services(TemplateCopy $t): array
    {
        $card = fn (int $n) => ['title' => $t('service_title', ['number' => $n]), 'text' => $t('service_text'), 'link_label' => $t('read_more'), 'url' => '#'];

        return ['type' => 'cards', 'data' => [
            'title' => $t('services_title'),
            'columns' => 3,
            'items' => [$card(1), $card(2), $card(3)],
            'style' => ['spacing' => 'lg'],
        ]];
    }

    protected static function testimonials(TemplateCopy $t): array
    {
        $item = fn (int $n) => ['quote' => $t("testimonial_{$n}"), 'name' => $t('person', ['number' => $n]), 'role' => $t('person_role')];

        return ['type' => 'testimonials', 'data' => [
            'title' => $t('testimonials_title'),
            'columns' => 3,
            'items' => [$item(1), $item(2), $item(3)],
            'style' => ['spacing' => 'lg', 'background' => 'muted'],
        ]];
    }

    protected static function faq(TemplateCopy $t): array
    {
        $items = [];

        foreach ([1, 2, 3, 4] as $n) {
            $items[] = ['question' => $t("faq_q{$n}"), 'answer' => $t("faq_a{$n}")];
        }

        return ['type' => 'faq', 'data' => [
            'title' => $t('faq_title'),
            'open_first' => true,
            'items' => $items,
            'style' => ['spacing' => 'lg'],
        ]];
    }

    protected static function logos(TemplateCopy $t): array
    {
        return ['type' => 'logos', 'data' => [
            'title' => $t('logos_title'),
            'grayscale' => true,
            'images' => [],
            'style' => ['spacing' => 'md'],
        ]];
    }

    protected static function callToAction(TemplateCopy $t): array
    {
        return ['type' => 'call_to_action', 'data' => [
            'title' => $t('cta_title'),
            'text' => $t('cta_text'),
            'primary_label' => $t('contact_us'),
            'primary_url' => '#',
            'style' => ['background' => 'primary', 'align' => 'center', 'spacing' => 'xl'],
        ]];
    }

    protected static function contact(TemplateCopy $t): array
    {
        return ['type' => 'section', 'data' => [
            'layout' => '1-1',
            'gap' => 'lg',
            'valign' => 'center',
            'columns' => [
                ['blocks' => [
                    ['type' => 'heading', 'data' => ['text' => $t('contact_title'), 'level' => 'h2']],
                    ['type' => 'rich_text', 'data' => ['body' => '<p>'.e($t('contact_text')).'</p>']],
                ]],
                ['blocks' => [
                    ['type' => 'button', 'data' => ['label' => $t('contact_us'), 'url' => '#', 'variant' => 'primary', 'arrow' => true]],
                    ['type' => 'button', 'data' => ['label' => $t('call_us'), 'url' => 'tel:', 'variant' => 'outline']],
                ]],
            ],
            'style' => ['spacing' => 'lg', 'background' => 'muted'],
        ]];
    }
}
