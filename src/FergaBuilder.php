<?php

namespace Ferga\FilamentBuilder;

use Ferga\FilamentBuilder\Contracts\ContentStore;
use Ferga\FilamentBuilder\Contracts\HasBuilderContentStore;
use Ferga\FilamentBuilder\Rendering\BlockRenderer;
use Ferga\FilamentBuilder\Support\BlockTree;
use Ferga\FilamentBuilder\Templates\TemplateRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Central settings + helpers: locales, RTL, storage, fallback, copy-layout.
 * Bound as a singleton; reach it via app(FergaBuilder::class) or the facade.
 */
class FergaBuilder
{
    /** @var array<string, string>|null */
    protected ?array $locales = null;

    protected ?string $defaultLocale = null;

    /** @var array<int, string>|null */
    protected ?array $rtlLocales = null;

    protected ?bool $fallback = null;

    protected ?string $disk = null;

    public function __construct(protected BlockRegistry $registry) {}

    public function registry(): BlockRegistry
    {
        return $this->registry;
    }

    public function templates(): TemplateRegistry
    {
        return app(TemplateRegistry::class);
    }

    public function renderer(): BlockRenderer
    {
        return app(BlockRenderer::class);
    }

    // ---------------------------------------------------------------- locales

    /** @param  array<string, string>|array<int, string>  $locales  code => label, or list of codes */
    public function setLocales(array $locales): static
    {
        $normalized = [];

        foreach ($locales as $code => $label) {
            if (is_int($code)) {
                $code = $label;
                $label = $this->guessLocaleLabel($code);
            }

            $normalized[(string) $code] = (string) $label;
        }

        $this->locales = $normalized;

        return $this;
    }

    /** @return array<string, string> code => label */
    public function locales(): array
    {
        $locales = $this->locales ?? (array) config('ferga-builder.locales', []);

        if ($locales === []) {
            $locales = [app()->getLocale() => $this->guessLocaleLabel(app()->getLocale())];
        }

        return $locales;
    }

    /** @return array<int, string> */
    public function localeCodes(): array
    {
        return array_keys($this->locales());
    }

    public function localeLabel(string $locale): string
    {
        return $this->locales()[$locale] ?? $this->guessLocaleLabel($locale);
    }

    public function setDefaultLocale(?string $locale): static
    {
        $this->defaultLocale = $locale;

        return $this;
    }

    public function defaultLocale(): string
    {
        return $this->defaultLocale
            ?? config('ferga-builder.default_locale')
            ?? array_key_first($this->locales());
    }

    /** @param  array<int, string>  $locales */
    public function setRtlLocales(array $locales): static
    {
        $this->rtlLocales = array_values($locales);

        return $this;
    }

    /** @return array<int, string> */
    public function rtlLocales(): array
    {
        return $this->rtlLocales ?? (array) config('ferga-builder.rtl_locales', []);
    }

    public function isRtl(?string $locale): bool
    {
        if (blank($locale)) {
            return false;
        }

        $locale = strtolower(str_replace('-', '_', $locale));
        $language = Str::before($locale, '_');

        foreach ($this->rtlLocales() as $rtl) {
            $rtl = strtolower(str_replace('-', '_', $rtl));

            if ($locale === $rtl || $language === $rtl) {
                return true;
            }
        }

        return false;
    }

    public function direction(?string $locale): string
    {
        return $this->isRtl($locale) ? 'rtl' : 'ltr';
    }

    /** BCP 47 tag for the `lang` attribute (cn → zh-CN, ku → ckb, zh_CN → zh-CN). */
    public function htmlLang(string $locale): string
    {
        return match (strtolower($locale)) {
            'cn' => 'zh-CN',
            'ku' => 'ckb',
            default => str_replace('_', '-', $locale),
        };
    }

    public function setFallback(bool $fallback): static
    {
        $this->fallback = $fallback;

        return $this;
    }

    public function fallbackEnabled(): bool
    {
        return $this->fallback ?? (bool) config('ferga-builder.fallback', true);
    }

    // ---------------------------------------------------------------- files

    public function setDisk(?string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function disk(): string
    {
        return $this->disk ?? config('ferga-builder.disk', 'public');
    }

    public function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//', '/', 'data:'])) {
            return $path;
        }

        return Storage::disk($this->disk())->url($path);
    }

    /**
     * All file paths referenced by a document or a locale map.
     *
     * @return array<int, string>
     */
    public function files(array $blocksOrDocuments): array
    {
        $documents = $this->isLocaleMap($blocksOrDocuments) ? $blocksOrDocuments : ['_' => $blocksOrDocuments];
        $files = [];

        foreach ($documents as $blocks) {
            BlockTree::walk((array) $blocks, function (array $block) use (&$files) {
                $type = $this->registry->get($block['type'] ?? null);

                foreach ($type?->fileFields() ?? [] as $key) {
                    foreach ((array) data_get($block, 'data.'.$key) as $path) {
                        if (is_string($path) && $path !== '') {
                            $files[] = $path;
                        }
                    }
                }
            });
        }

        return array_values(array_unique($files));
    }

    // ---------------------------------------------------------------- documents

    /**
     * True when the array is keyed by locale (['en' => [...], ...]) rather
     * than a plain list of blocks.
     */
    public function isLocaleMap(mixed $value): bool
    {
        if (! is_array($value) || $value === [] || array_is_list($value)) {
            return false;
        }

        // A single block ['type' => …, 'data' => …] is not a locale map.
        if (isset($value['type']) && is_string($value['type'])) {
            return false;
        }

        foreach ($value as $key => $blocks) {
            if (! is_string($key) || ! (is_array($blocks) || $blocks === null)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Normalize raw stored content into a locale map.
     * A plain list (legacy, non-localized content) belongs to the default locale.
     *
     * @return array<string, array<int, array>>
     */
    public function normalizeDocuments(mixed $raw): array
    {
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }

        if (! is_array($raw) || $raw === []) {
            return [];
        }

        if (! $this->isLocaleMap($raw)) {
            return [$this->defaultLocale() => BlockTree::clean($raw)];
        }

        $documents = [];

        foreach ($raw as $locale => $blocks) {
            $documents[$locale] = BlockTree::clean((array) ($blocks ?? []));
        }

        return $documents;
    }

    /**
     * Pick the blocks for a locale, falling back to the default locale when
     * that locale has none.
     *
     * @return array{blocks: array<int, array>, locale: string, fallback: bool}
     */
    public function resolve(array $documents, ?string $locale = null, ?bool $fallback = null): array
    {
        $documents = $this->isLocaleMap($documents) || $documents === []
            ? $documents
            : [$locale ?? $this->defaultLocale() => $documents];

        $locale ??= app()->getLocale();
        $fallback ??= $this->fallbackEnabled();

        if (! empty($documents[$locale])) {
            return ['blocks' => array_values($documents[$locale]), 'locale' => $locale, 'fallback' => false];
        }

        if ($fallback) {
            $default = $this->defaultLocale();

            foreach (array_unique([$default, ...$this->localeCodes()]) as $candidate) {
                if (! empty($documents[$candidate])) {
                    return ['blocks' => array_values($documents[$candidate]), 'locale' => $candidate, 'fallback' => true];
                }
            }
        }

        return ['blocks' => [], 'locale' => $locale, 'fallback' => false];
    }

    /**
     * Copy the blocks of one locale into another (new block ids). The target
     * locale's blocks are replaced; translators then edit the text in place.
     *
     * @return array<string, array<int, array>>
     */
    public function copyLayout(array $documents, string $from, string $to): array
    {
        $documents[$to] = BlockTree::refreshIds($documents[$from] ?? []);

        return $documents;
    }

    // ---------------------------------------------------------------- storage

    public function storeFor(Model $record, string $field): ContentStore
    {
        if ($record instanceof HasBuilderContentStore && ($store = $record->builderContentStore($field))) {
            return $store;
        }

        return app(config('ferga-builder.store', Storage\JsonColumnStore::class));
    }

    /** @return array<string, array<int, array>> */
    public function load(Model $record, string $field, ?ContentStore $store = null): array
    {
        return ($store ?? $this->storeFor($record, $field))->load($record, $field);
    }

    public function save(Model $record, string $field, array $documents, ?ContentStore $store = null): void
    {
        $clean = [];

        foreach ($documents as $locale => $blocks) {
            $clean[$locale] = BlockTree::clean((array) $blocks);
        }

        ($store ?? $this->storeFor($record, $field))->save($record, $field, $clean);
    }

    /** Blocks of a record for a locale (with fallback). */
    public function blocksFor(Model $record, string $field = 'content', ?string $locale = null): array
    {
        return $this->resolve($this->load($record, $field), $locale)['blocks'];
    }

    protected function guessLocaleLabel(string $code): string
    {
        return match (strtolower($code)) {
            'en' => 'English',
            'ar' => 'العربية',
            'ku', 'ckb' => 'کوردی',
            'kmr' => 'Kurdî',
            'cn', 'zh', 'zh_cn' => '中文',
            'fa' => 'فارسی',
            'tr' => 'Türkçe',
            'fr' => 'Français',
            'de' => 'Deutsch',
            'es' => 'Español',
            'ru' => 'Русский',
            'he' => 'עברית',
            'ur' => 'اردو',
            default => strtoupper($code),
        };
    }
}
