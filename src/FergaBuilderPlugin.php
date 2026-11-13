<?php

namespace Ferga\FilamentBuilder;

use Ferga\FilamentBuilder\Templates\Template;
use Filament\Contracts\Plugin;
use Filament\Panel;

/**
 * $panel->plugin(
 *     FergaBuilderPlugin::make()
 *         ->locales(['en' => 'English', 'ar' => 'العربية', 'ku' => 'کوردی', 'cn' => '中文'])
 *         ->defaultLocale('en')
 *         ->rtlLocales(['ar', 'ku'])
 * );
 *
 * Settings made here apply to every request that boots the panel. For the
 * public site, put the same settings in config/ferga-builder.php (the plugin
 * falls back to that config for anything not set here).
 */
class FergaBuilderPlugin implements Plugin
{
    protected ?array $locales = null;

    protected ?string $defaultLocale = null;

    protected ?array $rtlLocales = null;

    protected ?array $blocks = null;

    protected array $extraBlocks = [];

    protected ?array $templates = null;

    protected array $extraTemplates = [];

    protected ?bool $fallback = null;

    protected ?string $disk = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static */
        return filament(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'ferga-builder';
    }

    public function locales(array $locales): static
    {
        $this->locales = $locales;

        return $this;
    }

    public function defaultLocale(string $locale): static
    {
        $this->defaultLocale = $locale;

        return $this;
    }

    public function rtlLocales(array $locales): static
    {
        $this->rtlLocales = $locales;

        return $this;
    }

    /** Replace the block list. */
    public function blocks(array $blocks): static
    {
        $this->blocks = $blocks;

        return $this;
    }

    /** Add blocks to the configured list. */
    public function registerBlocks(array $blocks): static
    {
        $this->extraBlocks = [...$this->extraBlocks, ...$blocks];

        return $this;
    }

    /**
     * Replace the template list (false: no templates at all).
     *
     * @param  array<int, Template>|false  $templates
     */
    public function templates(array|false $templates): static
    {
        $this->templates = $templates === false ? [] : $templates;

        return $this;
    }

    /** @param  array<int, Template>  $templates */
    public function registerTemplates(array $templates): static
    {
        $this->extraTemplates = [...$this->extraTemplates, ...$templates];

        return $this;
    }

    public function fallback(bool $fallback = true): static
    {
        $this->fallback = $fallback;

        return $this;
    }

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function register(Panel $panel): void
    {
        $this->apply();
    }

    public function boot(Panel $panel): void
    {
        $this->apply();
    }

    protected function apply(): void
    {
        $builder = app(FergaBuilder::class);

        if ($this->locales !== null) {
            $builder->setLocales($this->locales);
        }

        if ($this->defaultLocale !== null) {
            $builder->setDefaultLocale($this->defaultLocale);
        }

        if ($this->rtlLocales !== null) {
            $builder->setRtlLocales($this->rtlLocales);
        }

        if ($this->fallback !== null) {
            $builder->setFallback($this->fallback);
        }

        if ($this->disk !== null) {
            $builder->setDisk($this->disk);
        }

        if ($this->blocks !== null) {
            $builder->registry()->set($this->blocks);
        }

        if ($this->extraBlocks !== []) {
            $builder->registry()->registerMany($this->extraBlocks);
        }

        if ($this->templates !== null) {
            $builder->templates()->set($this->templates);
        }

        if ($this->extraTemplates !== []) {
            $builder->templates()->registerMany($this->extraTemplates);
        }
    }
}
