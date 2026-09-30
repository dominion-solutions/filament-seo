# AGENTS.md

Guidance for AI coding agents working in this repository. Human contributors should read
[CONTRIBUTING.md](CONTRIBUTING.md) instead; the rules below are the same, aimed at an agent that
cannot infer them from context.

## What this package is

`dominion-solutions/filament-seo` is a Filament plugin that gives panel pages and the records
behind them SEO metadata, stored in one polymorphic table and rendered into the panel `<head>`.

It is a **library**, not an application. There is no `app/`, no routes file, and no
`bootstrap/app.php` — anything that looks like application structure is something you added.

## Setup

```bash
composer install
```

PHP 8.2+. The test suite runs on [Testbench](https://packages.tools/testbench), so there is no
database to set up and no `.env` to write. If a test needs a table, add a migration under
`tests/database/` and load it from `tests/TestCase.php`.

## Verify your work

```bash
composer check
```

That is `lint` + `analyse` + `test`, the core code gate CI runs as well. CI also validates the
package metadata, committed diagrams, and documentation links. If you are going to claim a change
works, this command has to pass — a claim without a green run is not a result.

Faster loops while iterating:

```bash
XDEBUG_MODE=off vendor/bin/phpunit --filter SomeTest
XDEBUG_MODE=off php -d memory_limit=1G vendor/bin/phpstan analyse --no-progress
XDEBUG_MODE=off vendor/bin/pint --test
```

## Non-negotiables

1. **Never commit a suppression to silence a real finding.** No `@phpstan-ignore`, no
   `ignoreErrors` entries, no `@phpstan-var` cast that papers over a genuine type error, no baseline
   file. Fix the code, or write a precise annotation that explains why the tool is wrong. See
   [CONTRIBUTING.md](CONTRIBUTING.md#types-and-static-analysis).

2. **`composer.lock` stays uncommitted.** It is gitignored on purpose; a library does not ship one.
   CI resolves against lowest and highest dependency sets instead.

3. **Do not push to `main`.** It is protected. Work on a branch.

4. **Keep `SeoData` the boundary.** It is `final readonly` and knows nothing about Filament,
   Eloquent or HTTP. New producers (something that produces a `SeoData`) and new renderers
   (something that turns one into markup) are fine; making `SeoData` know about the outside world
   is not.

5. **Diagrams are PlantUML sources, committed as rendered PNGs.** Edit `docs/diagrams/*.puml`, then
   run `composer diagrams` and commit both the `.puml` and the regenerated `.png`. Never hand-edit a
   PNG. CI fails if they are out of sync.

6. **Update the docs with the change.** Behaviour that a user can observe is documented in `docs/`,
   and — where it is a fix — in `CHANGELOG.md` under `Unreleased`.

## Where things live

| Path | What is in it |
| --- | --- |
| `src/Support/` | `SeoData`, `MetaTags`, `Text` — the framework-agnostic core |
| `src/Contracts/` | `HasSeo`, the model contract |
| `src/Concerns/` | Model and page traits |
| `src/Filament/Concerns/` | Filament-specific traits (form, actions) |
| `src/Schemas/` | `SeoSchema`, the form section |
| `tests/Fixtures/` | Testbench fixtures, which also serve as PHPStan analysis fixtures |
| `docs/diagrams/` | PlantUML sources; rendered output goes to `docs/assets/diagrams/` |

## Testing conventions

- Fixtures in `tests/Fixtures/` use the traits they analyse. If a trait has no fixture, Larastan
  will not analyse it and will report `trait.unused` — that is the mechanism, not a bug to suppress.
- Testbench fixtures are analysed at level 7 like everything else, so annotate `@property` for
  Eloquent columns rather than casting to a generic `Model`.
- When a method is `protected` but is really the thing under test, subclass it in
  `tests/Fixtures/TestablePlugin.php` rather than widening the production signature.

## Style

- Pint's `laravel` preset plus the rules pinned in `pint.json`. Run `composer fix`; do not
  hand-format, and do not add per-file `// pint:ignore` unless a line genuinely cannot be expressed.
- Comments explain *why*. If a comment restates the code, delete it. If a block needs a paragraph of
  prose to justify itself, extract a method and name it instead.
- Prefer naming a concept over repeating a type: a named method beats an inline comment every time.

## Verifying an example is real

Documentation examples are code. If you write an example, make sure it would actually run against
the installed Filament version — check the property types and method signatures rather than assuming.
Two real bugs in this repository came from examples that were never executed: a `protected string
$heading` that `Filament\Pages\BasePage` declares as `?string` (a fatal, since PHP does not allow
narrowing a property type), and an import of `Filament\Tables\Actions\CreateAction` that does not
exist.
