# Configuration

Publish the config with:

```bash
php artisan vendor:publish --tag=filament-seo
```

Every option has a working default, so an unconfigured installation is a valid one.

## `model`

```php
'model' => DominionSolutions\FilamentSeo\Models\Seo::class,
```

The model used to store per-record SEO metadata. Swap it for a subclass of `Seo` if you need extra
columns or different casts. Keep the `seoable` morph-to relation and the `robots` and `type` columns
as `NOT NULL` — the package treats both as always present. The `InteractsWithSeo` trait uses this
class for its `MorphOne` relation.

## `render_title`

```php
'render_title' => false,
```

Whether the package emits a `<title>` element.

Filament's layout already renders a `<title>` from the page title, and
[`HasSeoMetadata`](filament.md#pages) overrides `getTitle()` to return your SEO title. That is why
this defaults to `false`: leaving it on would produce **two** `<title>` tags and search engines
would pick one at random.

Set it to `true` only when you are using a layout that emits no title of its own.

## `canonical_fallback`

```php
'canonical_fallback' => true,
```

When a page does not set its own canonical URL, fall back to the current request URL.

This is a genuine win for a panel, where the same record is often reachable by more than one route
and you would otherwise be guessing. Turn it off if your panel is behind a path that is not the
canonical public URL — behind a versioned sub-path, for example — where the request URL would be
actively wrong rather than merely unhelpful.

Note that a page which sets its own canonical URL keeps it either way: this only fills a gap.

## `site_name`

```php
'site_name' => null,
```

The value used for the Open Graph `og:site_name` tag. Falls back to `config('app.name')` when
`null`.

Set this when the application name is not the public name — `"Acme Console"` for an internal tool
that is publicly branded `"Acme"`, for example.

## Where config does not belong

Not everything is configuration. Per-page and per-record metadata is code and data respectively,
not config, and the precedence is deliberate:

- A **page** setting its own site name or canonical URL keeps it, regardless of these values.
- A **panel-level** `seoData()` is used only for pages that publish nothing.
- **A record's** authored metadata always wins over anything generated from the model.

See [Architecture](architecture.md#metadata-precedence) for the resolution order.
