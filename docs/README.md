# Documentation

SEO metadata for [Filament](https://filamentphp.com) panels and the models behind them.

Filament panels are usually behind authentication, which makes them easy to forget about when
auditing a site. This package makes every panel page, resource and record describe itself: a
description and canonical URL are derived from the record itself, so nothing is invisible by
accident, and an editor can refine any of it later without touching code.

## Contents

| Guide | What it covers |
| --- | --- |
| [Installation](installation.md) | Composer, the plugin, the migration |
| [Configuration](configuration.md) | Every config option, and when to reach for it |
| [Models](models.md) | `InteractsWithSeo`, the `HasSeo` contract, and generated fallbacks |
| [Filament](filament.md) | Panel defaults, pages, resource forms, modal actions |
| [Architecture](architecture.md) | How the pieces fit together, with diagrams |

## The short version

```php
// 1. Register the plugin on your panel.
->plugins([
    FilamentSeoPlugin::make(),
])

// 2. Let your model describe itself.
class Product extends Model implements HasSeo
{
    use InteractsWithSeo;
}

// 3. Optionally, give a page its own metadata.
class About extends Page
{
    use HasSeoMetadata;

    protected static string $seoTitle = 'About Dominion Solutions';

    protected static string $seoDescription = 'Who we are and how we work.';
}
```

That is enough for a panel to emit description, robots, canonical, Open Graph and Twitter Card
tags. See [Models](models.md) for what "describes itself" actually means.

## How the value is chosen

When a page renders, exactly one `SeoData` value is assembled and rendered. The most specific
source wins, and the general ones only ever fill gaps:

1. **The page**, if it published metadata from a lifecycle hook.
2. **The panel**, if `seoData()` was configured on the plugin.
3. **`site_name` / `app.name`** and the current request URL, as a base layer underneath both.

A page that names its own site or canonical URL keeps it — a panel default is never written over a
page's own value. [Architecture](architecture.md#metadata-precedence) has the full picture.

## Rendering the `<title>` element

By default the package does **not** emit a `<title>`. Filament already renders one from the page
title, and `HasSeoMetadata` points that at your SEO title — so exactly one `<title>` ends up in the
head. Set `filament-seo.render_title` to `true` only if you are using a layout that emits none.
