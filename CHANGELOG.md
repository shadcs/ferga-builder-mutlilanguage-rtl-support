# Changelog

All notable changes to `ferga/filament-builder` are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [0.1.0] - 2026-11-13

### Added
- Templates: 11 ready-made sections (three hero layouts, features, our story + numbers, services, testimonials, FAQ, client logos, call to action, contact strip) and 4 full pages (landing, product, about, services). Insert them from the canvas (Templates tab: click or drag) or the form field (Insert template). Sample text is translated and inserted in the language being edited. Register your own with `Template::make()`; manage them with `registerTemplates()` / `templates()` on the plugin, or turn off the built-in ones with the `core_templates` config.
- Section blocks: hero (centered, text + image, text over a photo), call to action, FAQ (native `<details>`), cards, testimonials and logos.
- `fileFields()` supports nested paths such as `items.*.image`.
- Pint code style and a CI job for it; issue and pull request templates; CONTRIBUTING and SECURITY docs; screenshots in `art/`.
- `PageBlock` contract, `Blocks\Block` base class and `BlockRegistry`.
- Core blocks: section/columns, heading, rich text, image, image + text, gallery, video (YouTube/Vimeo → privacy-friendly embed), quote, button, highlights (with icons), numbers/stats, file download, divider, spacer.
- Style tokens per block (spacing, width, alignment, background) rendered as classes.
- Per-locale content with fallback to the default locale; `ContentStore` interface with `JsonColumnStore` (default), `ColumnPerLocaleStore` and `CallbackStore` adapters; per-model override via `HasBuilderContentStore`.
- `PageBuilder` form field: one tab per language, RTL-aware inputs, "Copy layout from another language" action, fallback notice.
- `BuilderCanvas` full-screen visual editor: drag-and-drop palette, sections/columns, outline, inspector with style tokens and column layouts, undo/redo, device preview widths, locale switcher, copy layout, keyboard shortcuts, save.
- `OpenCanvasAction` header action.
- Public renderer `<x-ferga-builder::blocks>` with `dir`/`lang` per locale, unknown types skipped, and `<x-ferga-builder::styles>`.
- Prebuilt flat CSS (no shadows) with CSS variables and logical properties; directional icons mirror in RTL.
- Editor translations: English, Arabic, Kurdish Sorani (`ku`, `ckb`), Simplified Chinese (`zh_CN`, `cn`, `zh`).
- `FergaBuilderPlugin` panel plugin and publishable config/views/translations/assets.
