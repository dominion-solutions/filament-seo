# Installation

## Requirements

- PHP 8.3 or higher
- Laravel 11, 12 or 13
- Filament 4.9 or 5
- Livewire 3 or 4

## Install the package

```bash
composer require dominion-solutions/filament-seo
```

## Add the migration

The package ships one migration that creates the `seos` table. It is loaded by the service
provider, so there is nothing to publish for the table itself.

You do need to run it:

```bash
php artisan migrate
```

## Register the plugin

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

## Publish the config

The config file is optional — every value has a working default. Publish it when you want to change
something:

```bash
php artisan vendor:publish --tag=filament-seo
```

The tag publishes both `config/filament-seo.php` and the migration into `database/migrations`.
Publishing is optional; it is only useful if your team prefers to own its migration files. The
package also loads its migration directly, and Laravel tracks migrations by name, so the published
copy will not run twice. There is no separate config-only publish tag.

See [Configuration](configuration.md) for what each option does.

## Verify it

Visit any panel page and view source. You should see a `<meta name="description">` and a
`<meta name="robots">` even if you have written no code beyond the plugin registration.

If the tags are missing, check that the plugin is registered on the panel you are actually looking
at — panels are configured separately, and it is easy to register the plugin on one and not another.

## Optional: make your models describable

The plugin is useful on its own, but a model that can describe itself gives every page showing that
record real metadata with no further work:

```php
use DominionSolutions\FilamentSeo\Concerns\InteractsWithSeo;
use DominionSolutions\FilamentSeo\Contracts\HasSeo;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements HasSeo
{
    use InteractsWithSeo;
}
```

See [Models](models.md) for what this generates and how to override it.

## Upgrading

The package follows semantic versioning. Bump the constraint in `composer.json`, run
`composer update dominion-solutions/filament-seo`, and re-read the changelog for the release — the
`seos` table schema is part of that contract, so any change to it is a major release.
