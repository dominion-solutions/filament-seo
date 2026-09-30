# Filament SEO

Per-record SEO metadata for [Laravel](https://laravel.com) and [Filament](https://filamentphp.com) apps, exposed as form fields and rendered as a clean `<head>`.

Every indexable page gets its own title and meta description — authored in the admin panel, or derived from the record when an editor has not written one.

[![CI](https://github.com/dominion-solutions/filament-seo/actions/workflows/ci.yml/badge.svg)](https://github.com/dominion-solutions/filament-seo/actions/workflows/ci.yml)
[![Packagist](https://img.shields.io/packagist/v/dominion-solutions/filament-seo)](https://packagist.org/packages/dominion-solutions/filament-seo)
[![License](https://img.shields.io/packagist/l/dominion-solutions/filament-seo)](LICENSE)

## What it does

- Stores title, description, canonical URL, image, `robots` and Open Graph type in a single polymorphic table, so it works with any model.
- Adds a reusable `Section` to Filament create/edit forms, with sensible defaults and no extra wiring per resource.
- Renders `description`, `robots`, `canonical`, Open Graph and Twitter tags from a Filament render hook — no Blade view to publish.
- Falls back to values you derive from the record (`getSeoData()`), so a new product or article is indexable before anyone touches the admin panel.
- Leaves records that have never been given metadata untouched: no row, no behaviour change.

## Requirements

| | |
|---|---|
| PHP | 8.2+ |
| Laravel | 11.28, 12 or 13 |
| Filament | 5 |
| Livewire | 4.4+ |
| Tailwind CSS | 4.1+ (Filament's requirement) |

## Installation

```bash
composer require dominion-solutions/filament-seo
php artisan migrate
```

Publish the config if you want to change defaults. This copies the config file and the migration
into your application; the package loads its own migration either way.

```bash
php artisan vendor:publish --tag=filament-seo
```

Register the plugin on each panel that renders public pages:

```php
use DominionSolutions\FilamentSeo\FilamentSeoPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentSeoPlugin::make(),
        ]);
}
```

> The plugin deliberately does **not** render a `<title>` element. Filament already renders one
> from the page's title, and `HasSeoMetadata` points that at your SEO title — so exactly one
> `<title>` ends up in the head. Two titles is worse than none. Set `render_title` to `true` in the
> config only if your layout emits no title of its own.

## Storing metadata on a model

Add the trait to any model you want to describe, and declare the `HasSeo` contract so the metadata
is nameable and type-hintable:

```php
use DominionSolutions\FilamentSeo\Concerns\InteractsWithSeo;
use DominionSolutions\FilamentSeo\Contracts\HasSeo;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements HasSeo
{
    use InteractsWithSeo;
}
```

`getSeoData()` returns the effective metadata: your stored row where it has a value, falling back to
whatever the overrides below provide. `saveSeo()` creates the row on first save, and updates it in
place after that.

To give a model sensible defaults, override the fallbacks:

```php
use DominionSolutions\FilamentSeo\Support\Text;

protected function getSeoFallbackTitle(): ?string
{
    return $this->name;
}

protected function getSeoFallbackDescription(): ?string
{
    return Text::excerpt($this->tagline);
}

protected function getSeoFallbackImageUrl(): ?string
{
    return $this->screenshot_url;
}
```

`getSeoFallbackImageAlt()`, `getSeoFallbackCanonicalUrl()` and `getSeoFallbackType()` work the same
way. `robots` has no fallback hook — it is `index, follow` until an editor changes it. The `Text`
helper strips markup and clips a string to a budget that fits a meta description — use
`Text::fromMarkdown()` when the source is Markdown.

With no overrides at all, the trait uses the first attribute it finds: `name`, `title` or `headline`
for the title, and `description`, `summary`, `excerpt`, `tagline`, `body` or `content` for the
description.

## Editing metadata in Filament

Add the schema to a resource form:

```php
use DominionSolutions\FilamentSeo\Schemas\SeoSchema;
use Filament\Schemas\Schema;

public static function form(Schema $schema): Schema
{
    return $schema->components([
        // ...
        SeoSchema::make(),
    ]);
}
```

That is the current Filament resource API; see the [Filament guide](docs/filament.md#the-schema)
for the surrounding form, and for what the plugin does on a panel with no resource.

Then hook persistence into the create and edit pages:

```php
use DominionSolutions\FilamentSeo\Filament\Concerns\CreatesSeoMetadata;
use DominionSolutions\FilamentSeo\Filament\Concerns\UpdatesSeoMetadata;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;

class CreateProduct extends CreateRecord
{
    use CreatesSeoMetadata;
}

class EditProduct extends EditRecord
{
    use UpdatesSeoMetadata;
}
```

The `seo.*` form state is captured and stripped before the record is saved, so the nested array never reaches the model's attributes.

For records created or edited in a **modal** — a relation manager, or a `CreateAction`/`EditAction` — there are no resource page hooks to use. `InteractsWithSeoActions` covers those:

```php
use DominionSolutions\FilamentSeo\Filament\Concerns\InteractsWithSeoActions;
use DominionSolutions\FilamentSeo\Schemas\SeoSchema;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CaseStudiesRelationManager extends RelationManager
{
    use InteractsWithSeoActions;

    // A relation on the owning model; replace with your actual relationship name.
    protected static string $relationship = 'caseStudies';

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make('createCaseStudy')
                    ->form([SeoSchema::make()])
                    ->mutateDataUsing($this->captureSeoFormData(...))
                    ->after(fn (Model $record) => $this->applyPendingSeoFormData($record)),
            ]);
    }
}
```

On edit actions, load the existing values with `fillSeoFormData(...)` in `fillForm()`.

## Describing a page

Public Filament pages hand their metadata to the render hook with `HasSeoMetadata`:

```php
use DominionSolutions\FilamentSeo\Concerns\HasSeoMetadata;
use Filament\Pages\Page;

class About extends Page
{
    use HasSeoMetadata;

    protected ?string $heading = '';

    protected static ?string $seoTitle = 'About Us';

    protected static ?string $seoDescription = 'Twenty years of engineering leadership, applied to software small businesses can rely on.';
}
```

> `$heading` is `?string`, because that is how Filament declares it. Declaring it as `string` is a
> fatal error — PHP does not allow a subclass to narrow a property's type. Set it whenever the page
> renders a heading, or Filament will fall back to `getTitle()` and put your SEO title in the page
> body as well as the `<head>`.

Add a separator at the end of `getTitle()` for longer titles, e.g. `'About Us | My Company'`. If a
computed title needs to return an `Htmlable`, override `getSeoTitle(): string|Htmlable`; the
description property is a string.

For a page that renders a record, override `getSeoData()` and fall back to the record's metadata — the hook picks up whatever it returns:

```php
public function getSeoData(): SeoData
{
    return $this->record->getSeoData()->merge([
        'type' => SeoData::TYPE_ARTICLE,
    ]);
}
```

## Panel-wide defaults

Metadata that applies to a whole panel goes in the config's `site_name`, or per page:

```php
FilamentSeoPlugin::make()
    ->seoData([
        'siteName' => 'Acme',
        'imageUrl' => asset('images/acme.png'),
    ]);
```

`seoData()` also accepts a `SeoData`, or a closure returning either — use a closure for
multi-tenant panels, where the metadata depends on the current tenant.

Resolution order is page, then panel, then the request URL for canonical and `app.name` for the site
name. The last two are layered *underneath* rather than merged over, so a page that sets its own site
name or canonical URL keeps it.

## Documentation

Detailed guides live in [`docs/`](docs):

| Guide | What it covers |
| --- | --- |
| [Installation](docs/installation.md) | Composer, the plugin, the migration |
| [Configuration](docs/configuration.md) | Every config option, and when to reach for it |
| [Models](docs/models.md) | `InteractsWithSeo`, the `HasSeo` contract, generated fallbacks |
| [Filament](docs/filament.md) | Panel defaults, pages, resource forms, modal actions |
| [Architecture](docs/architecture.md) | How the pieces fit together, with diagrams |

## Testing

```php
use DominionSolutions\FilamentSeo\Support\SeoData;

$product->saveSeo(['title' => 'A better title']);

expect($product->fresh()->getSeoData()->title)->toBe('A better title');
```

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). In short: `composer install`, then `composer check` for
lint, static analysis and tests.

## License

MIT.
