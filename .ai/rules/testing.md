---
id: testing
description: Testbench conventions, and why fixtures double as analysis fixtures
applies_to: ["tests/**"]
severity: error
---

# Testing

The suite runs on [Testbench](https://packages.tools/testbench). There is no application, no
`.env`, and no database to provision: `tests/TestCase.php` registers the package's service
provider and loads migrations from `tests/database/`.

## Fixtures are analysis fixtures

`tests/Fixtures/` is on Larastan's path list. A fixture that uses `InteractsWithSeo` is not just
exercising the trait at runtime — it is the reason Larastan analyses the trait at all. A trait with
no fixture is reported as `trait.unused`.

That coupling is the point. If a fixture stops using a trait, the trait quietly stops being checked.

## Conventions

- Fixtures are `final` Testbench subjects with `@property` annotations for their columns, not casts
  to a generic `Model`.
- A new table belongs in `tests/database/`, with a `0000_00_00_000000_` filename so it sorts ahead
  of the package's own migration.
- Behavioural fix → a test that fails without the fix.
- New public method → a test that exercises it.

## Reaching protected methods

When the thing under test is `protected`, subclass rather than widening the production signature:

```php
class TestablePlugin extends FilamentSeoPlugin
{
    public function resolvedSeoData(): SeoData
    {
        return $this->resolveSeoData();
    }
}
```

## Local runs

```bash
XDEBUG_MODE=off vendor/bin/phpunit
XDEBUG_MODE=off vendor/bin/phpunit --filter ModelMetadataTest
```
