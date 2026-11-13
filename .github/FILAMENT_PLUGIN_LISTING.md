# Filament plugin directory submission

Plugins are submitted at **https://filamentphp.com/author** (request access to an author profile, then "Submit plugin"). This file has the values ready to paste.

## Before submitting

1. Create the public GitHub repository and push `main`. All URLs below use `ferga/filament-builder`; if the repo lives elsewhere, search and replace that string in this repo (README image URLs, `composer.json`, `.github/ISSUE_TEMPLATE/config.yml`).
2. Tag a release: `git tag v0.1.0 && git push --tags`.
3. Submit the package on https://packagist.org/packages/submit and enable the GitHub hook so new tags appear automatically.
4. Check that the README renders on GitHub, including the images in `art/`.

## Listing fields

| Field | Value |
| --- | --- |
| Name | Ferga-Builder |
| Slug | `ferga-builder` (shown as `ferga-ferga-builder` if the author slug is `ferga`) |
| Description | Drag-and-drop visual page builder with ready-made templates, per-language content and real RTL support (Arabic, Kurdish, Persian, Hebrew). Includes a full-screen canvas, a form field, 20 blocks, and an editor UI in English, Arabic, Kurdish and Chinese. |
| Categories | Form Builder, Form Field, Developer Tool, Kit (pick what the form offers) |
| GitHub repository | `ferga/filament-builder` |
| Documentation URL | `https://raw.githubusercontent.com/ferga/filament-builder/main/README.md` |
| Filament versions | 4, 5 |
| Dark theme | Yes |
| Translations | Yes (en, ar, ku/ckb, zh_CN/cn) |
| Price | Free (MIT) |
| Thumbnail | `art/cover.jpg` (2560 × 1440, 16:9) |

## Screenshots

- `art/cover.jpg`: canvas with the Templates tab and a block selected
- `art/canvas-templates.png`: templates palette
- `art/canvas-rtl.png`: Arabic page, mirrored layout
- `art/canvas-edit-modal.png`: block content form in a modal
- `art/canvas-dark.png`: dark mode
- `art/form-field.png`: form field with language tabs

## Short pitch (for the author profile, social posts)

> Ferga-Builder is a Filament page builder for multilingual sites. Each language has its own blocks, Arabic and Kurdish pages are truly right-to-left, and editors can start from 15 ready-made sections and pages that come with sample text in the language they are editing.
