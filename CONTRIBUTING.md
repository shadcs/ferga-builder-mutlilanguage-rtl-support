# Contributing

Thanks for helping improve Ferga-Builder.

## Setup

```bash
composer install
vendor/bin/pest        # tests (boots a real Filament panel with Testbench)
vendor/bin/pint        # code style
```

## Guidelines

- **Translations:** every new UI string goes into all language files in `resources/lang/` (`en`, `ar`, `ku`, `zh_CN`; `ckb`, `cn` and `zh` are aliases). If you can't translate a string, add the English text and mention it in the pull request.
- **RTL:** use CSS logical properties (`padding-inline-start`, `inset-inline-end`, `text-align: start`), never `left`/`right`. Arrows and chevrons get the `fb-icon-directional` class so they mirror.
- **Flat UI:** no shadows. Theme through the `--fb-*` CSS variables.
- **No build step:** CSS in `resources/dist/` is hand-written and shipped as is.
- **Blocks** must render with empty `data` without errors (there is a test for this).
- Add a line to `CHANGELOG.md` under *Unreleased*.
