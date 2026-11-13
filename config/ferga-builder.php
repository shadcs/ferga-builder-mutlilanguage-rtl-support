<?php

use Ferga\FilamentBuilder\Blocks\ButtonBlock;
use Ferga\FilamentBuilder\Blocks\CallToActionBlock;
use Ferga\FilamentBuilder\Blocks\CardsBlock;
use Ferga\FilamentBuilder\Blocks\DividerBlock;
use Ferga\FilamentBuilder\Blocks\FaqBlock;
use Ferga\FilamentBuilder\Blocks\FileDownloadBlock;
use Ferga\FilamentBuilder\Blocks\GalleryBlock;
use Ferga\FilamentBuilder\Blocks\HeadingBlock;
use Ferga\FilamentBuilder\Blocks\HeroBlock;
use Ferga\FilamentBuilder\Blocks\HighlightsBlock;
use Ferga\FilamentBuilder\Blocks\ImageBlock;
use Ferga\FilamentBuilder\Blocks\ImageTextBlock;
use Ferga\FilamentBuilder\Blocks\LogosBlock;
use Ferga\FilamentBuilder\Blocks\QuoteBlock;
use Ferga\FilamentBuilder\Blocks\RichTextBlock;
use Ferga\FilamentBuilder\Blocks\SectionBlock;
use Ferga\FilamentBuilder\Blocks\SpacerBlock;
use Ferga\FilamentBuilder\Blocks\StatsBlock;
use Ferga\FilamentBuilder\Blocks\TestimonialsBlock;
use Ferga\FilamentBuilder\Blocks\VideoBlock;
use Ferga\FilamentBuilder\Storage\JsonColumnStore;

return [

    /*
    | Content locales (code => native name). The first one is the default
    | unless `default_locale` is set. Codes are whatever your app uses
    | (e.g. "cn" or "zh_CN", "ku" or "ckb").
    */
    'locales' => [
        'en' => 'English',
    ],

    'default_locale' => null,

    /*
    | When a locale has no blocks, render the default locale's blocks instead.
    */
    'fallback' => true,

    /*
    | Locales written right-to-left. Prefix match on the language part, so
    | "ar" also covers "ar_IQ".
    */
    'rtl_locales' => ['ar', 'ku', 'ckb', 'fa', 'he', 'ur', 'ps', 'sd', 'yi', 'dv'],

    /*
    | Filesystem disk for uploaded images and files.
    */
    'disk' => 'public',

    'upload_directory' => 'ferga-builder',

    /*
    | Default storage for record content. Models can override per field by
    | implementing Ferga\FilamentBuilder\Contracts\HasBuilderContentStore.
    */
    'store' => JsonColumnStore::class,

    /*
    | Block types. Add your own classes implementing
    | Ferga\FilamentBuilder\Contracts\PageBlock (or extending Blocks\Block).
    */
    'blocks' => [
        SectionBlock::class,
        HeroBlock::class,
        HeadingBlock::class,
        RichTextBlock::class,
        ImageBlock::class,
        ImageTextBlock::class,
        GalleryBlock::class,
        VideoBlock::class,
        QuoteBlock::class,
        ButtonBlock::class,
        CallToActionBlock::class,
        HighlightsBlock::class,
        CardsBlock::class,
        StatsBlock::class,
        TestimonialsBlock::class,
        FaqBlock::class,
        LogosBlock::class,
        FileDownloadBlock::class,
        DividerBlock::class,
        SpacerBlock::class,
    ],

    /*
    | Ready-made section and page templates (hero, features, FAQ, landing
    | page, …). Set to false to hide the built-in ones; add your own with
    | FergaBuilderPlugin::make()->registerTemplates([...]).
    */
    'core_templates' => true,

    /*
    | Undo/redo depth of the visual canvas.
    */
    'history_limit' => 50,

    /*
    | Cache store holding canvas undo/redo history (null = default store).
    */
    'history_cache_store' => null,
];
