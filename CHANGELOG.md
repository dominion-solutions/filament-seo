# Changelog

All notable changes to `dominion-solutions/filament-seo` are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project
adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Versions come from git tags. Packagist reads the tag, so nothing here needs to agree with a version
number in `composer.json` — but every release gets an entry below.

## [Unreleased]

### Added

- Comprehensive documentation set in [`docs/`](docs), with PlantUML diagrams for the architecture,
  metadata precedence, head rendering, the authoring flow, the `SeoData` value object and storage.
- Testbench-based test suite covering plugin metadata resolution and model metadata assembly.
- `bin/render-diagrams.sh` for rendering and verifying the committed diagrams.
- `bin/check-doc-links.php` for verifying that relative links in the Markdown resolve.
- CI covering tests on the lowest and highest dependency sets, Larastan, Pint, diagram freshness,
  and Packagist readiness. Larastan runs against both dependency sets, so the declared floor is
  type-checked rather than only executed.
- Dependabot configuration for Composer and GitHub Actions dependencies.

### Changed

- **Filament 5 is the only supported Filament version.** Supporting 4.x meant carrying a second set
  of form and page signatures through the docs and the analysis fixtures, and the plugin never used
  anything that needed them. Dropping it also means the package can state the same floors Filament 5
  does — PHP 8.2+, Laravel 11.28+, Livewire 4.4+ — instead of floors derived from tooling accidents.
- **Metadata precedence is now page, then panel, then fallbacks.** Previously a panel-level
  `seoData()` took precedence over the page, and the `site_name` and canonical fallbacks were
  merged *over* the resolved value — so a page could never set its own site name or canonical URL.
  The fallbacks are now layered underneath, and the resolution order matches what the README has
  always claimed.

### Fixed

- `HasSeoMetadata` no longer fatals when a title or description is an `Htmlable`: it called
  `$value->toHtml()` on something cast to `string`, which threw instead of returning the value.
- `saveSeo()` now preserves authored fields omitted from a partial update instead of silently
  resetting them to `null`.
- Redundant `?->` in form field defaults removed. `??` already suppresses the error for the whole
  left-hand chain, so `$seo?->robots ?? $default` and `$seo->robots ?? $default` return the same
  thing; the shorter form says what actually happens.
- The dev floor for `orchestra/testbench` is 9.2. Testbench 9.0 and 9.1 assign to an undeclared
  static property in `setUp()`, which throws on PHP 8 — every test errored out on the lowest
  dependency set.
