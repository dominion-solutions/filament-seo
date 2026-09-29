# Filament SEO

Per-record SEO metadata for [Laravel](https://laravel.com) and [Filament](https://filamentphp.com) apps, exposed as form fields and rendered as a clean `<head>`.

Every indexable page gets its own title and meta description — authored in the admin panel, or derived from the record when an editor has not written one.

## What it does

- Stores title, description, canonical URL, image, `robots` and Open Graph type in a single polymorphic table, so it works with any model.
- Adds a reusable `Section` to Filament create/edit forms, with sensible defaults and no extra wiring per resource.
- Renders `<title>`, `description`, `robots`, `canonical`, Open Graph and Twitter tags from a Filament render hook — no Blade view to publish.
- Falls back to values you derive from the record (`getSeoData()`), so a new product or article is indexable before anyone touches the admin panel.
- Leaves records that have never been given metadata untouched: no row, no behaviour change.

## Requirements

| | |
|---|---|
| PHP | 8.3+ |
| Laravel | 11, 12 or 13 |
| Filament | 4 or 5 |
| Livewire | 3 or 4 |

## Installation

```bash
composer require dominion-solutions/filament-seo
php artisan migrate
```

Publish the config if you want to change defaults:

```bash
php artisan vendor:publish --tag=filament-seo
```

Migrations are shipped and loaded from the package, so `php artisan migrate` is all that is needed. The same tag will copy the migration into `database/migrations` if you would rather own it.

Register the plugin on each panel that renders public pages:

```php
use DominionSolutions\FilamentSeo\FilamentSeoPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->brandName('')
        ->plugins([
            FilamentSeoPlugin::make(),
        ]);
}
```

> The plugin deliberately does **not** render a `<title>` element. Filament already renders one from the page's title, and two titles is worse than none. Set `render_title` to `true` in the config if your layout emits no title of its own.

## Storing metadata on a model

Add the trait to any model you want to describe:

```php
use DominionSolutions\FilamentSeo\Concerns\InteractsWithSeo;

class Product extends Model
{
    use InteractsWithSeo;
}
```

`getSeoData()` returns the effective metadata: your stored row where it has a value, falling back to whatever the override below provides. It creates the row on first save, and updates it in place after that.

To give a model sensible defaults, override the fallbacks:

```php
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

`getSeoFallbackImageAlt()`, `getSeoFallbackCanonicalUrl()` and `getSeoFallbackType()` work the same way. `robots` has no fallback hook — it is `index, follow` until an editor changes it. The `Text` helper strips markup and clips a string to a budget that fits a meta description — use `Text::fromMarkdown()` when the source is Markdown.

## Editing metadata in Filament

Add the schema to a resource form:

```php
use DominionSolutions\FilamentSeo\Schemas\SeoSchema;

public function form(Schema $schema): Schema
{
    return $schema->components([
        // ...
        SeoSchema::make(),
    ]);
}
```

Then hook persistence into the create and edit pages:

```php
use DominionSolutions\FilamentSeo\Filament\Concerns\CreatesSeoMetadata;
use DominionSolutions\FilamentSeo\Filament\Concerns\UpdatesSeoMetadata;

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

class CaseStudiesRelationManager extends RelationManager
{
    use InteractsWithSeoActions;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make('createCaseStudy')
                    ->mutateDataUsing($this->captureSeoFormData(...))
                    ->after(fn (Model $record) => $this->applyPendingSeoFormData($record)),
            ]);
    }
}
```

## Describing a page

Public Filament pages hand their metadata to the render hook with `HasSeoMetadata`:

```php
use DominionSolutions\FilamentSeo\Concerns\HasSeoMetadata;

class About extends Page
{
    use HasSeoMetadata;

    protected string $heading = '';

    protected static ?string $seoTitle = 'About Us';

    protected static ?string $seoDescription = 'Twenty years of engineering leadership, applied to software small businesses can rely on.';
}
```

Add a separator at the end of `getTitle()` for longer titles, e.g. `'About Us | My Company'`. `seoTitle` and `seoDescription` also accept `Htmlable` values.

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
        'title' => 'Acme',
        'robots' => SeoData::ROBOTS_INDEX_FOLLOW,
    ]);
```

Resolution order is page, then panel, then the request URL for canonical and `app.name` for the site name.

## Testing

```php
use DominionSolutions\FilamentSeo\Support\SeoData;

$product->saveSeo(['title' => 'A better title']);

expect($product->fresh()->getSeoData()->title)->toBe('A better title');
```

## License

MIT.
