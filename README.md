# Filament SEO

Laravel + Filament SEO: clean page titles, unique meta descriptions, and Open
Graph / Twitter tags, managed from the Filament admin panel.

> **Status: in active development.** This package is being built to solve a
> concrete problem in
> [`dominion-solutions/website`](https://forgejo.git.internal.dominion.solutions/dominion-solutions/website)
> and is not released yet. The API may still change before `1.0`. See
> [Status](#status) below.

## The problem

Filament applications inherit two SEO defaults that are actively harmful on a
public-facing site:

1. **The `<title>` tag is built from the page title and the app name**, joined
   with a configurable separator. On a public panel that produces duplicated,
   suffix-laden titles like `Contact – Dominion Solutions` on every page, which
   wastes the most valuable on-page signal you have.
2. **There is no meta description at all**, so search engines invent one, and
   there is nothing to control Open Graph or Twitter previews with.

Meanwhile, content pages (products, case studies) need per-record titles and
descriptions that non-technical staff can edit — without a developer.

## What this package aims to provide

- **One clean `<title>` per page**, owned by your metadata, not by the panel.
- **One unique meta description per page**, on every indexable page.
- **Editor-managed SEO** for dynamic content, via a reusable Filament component
  that drops into any resource.
- **Declarative metadata for static pages**, so titles and descriptions live in
  one obvious place next to the page class rather than in a Blade view.
- **robots, canonical URL, Open Graph and Twitter tags** emitted consistently.
- **No opinion about your models.** The reusable component accepts any Filament
  image field for the share image, so the package does not require
  `spatie/laravel-medialibrary` (or any other media package).

## Status

The package is developed in-repo at
`dominion-solutions/website` under `packages/filament-seo`, and the source is
synced to this repository. Tracked work: issue #50 in the website repository.

Install and usage documentation will be added here once the public API settles
and a tagged release is cut.

## License

MIT. See [LICENSE](LICENSE).
