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

- The minimum supported Filament is now 4.9. Filament's resource pages (`CreateRecord`,
  `EditRecord`) are only generic from 4.8.1 onwards, and the package's own analysis fixtures depend
  on that to type the record without a suppression. A plugin that will not be installed alongside a
  year-old patch release is the smaller cost.
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
  static property in `setUp()`, which is a fatal error on PHP 8.3+ — every test errored out on the
  lowest dependency set.
