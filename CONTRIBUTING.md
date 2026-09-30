# Contributing

Thanks for considering a contribution. This is a small package with a deliberately small surface
area, so the bar is mostly "does the change make the thing simpler".

## Getting set up

```bash
git clone git@github.com:dominion-solutions/filament-seo.git
cd filament-seo
composer install
```

You need PHP 8.3+ and a JDK if you are going to touch the diagrams — the renderer shells out to
PlantUML.

## Before you push

The core quality gate CI runs is available locally:

```bash
composer check
```

which is:

| Command | What it does |
| --- | --- |
| `composer lint` | `pint --test` — formatting, no writes |
| `composer fix` | `pint` — applies formatting |
| `composer analyse` | Larastan at level 7 |
| `composer test` | PHPUnit |
| `composer diagrams` | Re-renders the PlantUML diagrams |
| `composer diagrams-check` | Verifies rendered PNGs match their PlantUML sources |
| `composer docs` | Verifies relative Markdown links resolve |

`composer check` is the core code gate: lint, analyse, test. CI also validates package metadata and
runs `composer diagrams-check` and `composer docs`. If you are adding or changing a diagram, run
`composer diagrams` and commit the regenerated PNGs — CI fails if they are stale.

## House style

The formatting is Pint's `laravel` preset, with a few rules pinned in `pint.json`. Run
`composer fix` and commit the result rather than hand-formatting; a `Code style` workflow also
pushes a formatting commit to your branch if you miss it.

Beyond formatting, a few conventions this codebase follows:

- **Comments explain *why*, not *what*.** The code says what it does. A comment earns its place by
  explaining a decision, a constraint, or a trap.
- **Docblocks on non-obvious public API, prose paragraphs in them.** Public methods get a summary
  and, where the reasoning is not obvious, a paragraph.
- **Prefer a named method to an inline comment.** If a block needs three lines of prose to explain,
  extract it and name it.
- **No new dependencies without discussion.** This package's value is that it is easy to adopt.
  Everything it needs it should be able to do with Laravel, Filament and Livewire.

## Types and static analysis

Larastan runs at **level 7** with no baseline and no `ignoreErrors`. That is deliberate: a library is
consumed by code that is type-checked, and an imprecise public signature becomes someone else's
problem.

In practice that means:

- Eloquent relations carry generics: `@return MorphOne<Seo, $this>`.
- Models that use `InteractsWithSeo` should also `implements HasSeo`, so the trait's methods are
  nameable and the behaviour is type-hintable.
- Arrays get `array<string, mixed>` or `list<...>` in their docblocks.

Level 8 passes. Level 9 is the `mixed` check, and it reports three groups of errors: `SeoData::make()`
spreading `mixed` values into its typed constructor parameters, `InteractsWithSeo` assigning `mixed`
into the `Seo` model's typed `$robots` and `$type`, and the Filament concerns passing Filament's
`array<mixed, mixed>` around where `array<string, mixed>` is declared. Fixing the first properly means
a typed builder or explicit named arguments at the call site, which is a design decision rather than a
lint fix. Raise the level when that gets done — do not suppress it to get there.

**Do not add suppression comments** (`@phpstan-ignore`, `@phpstan-var` casts used to silence a real
error, `ignoreErrors` entries) to make the build pass. If Larastan is right and the code is wrong,
change the code. If Larastan is wrong, the answer is a precise annotation that explains why, or a
narrower signature.

## Tests

`orchestra/testbench` runs the package inside a real Laravel application. Fixtures live in
`tests/Fixtures` and double as static-analysis fixtures — that is why they use the traits they use.

Both layers are expected to grow together:

- A behavioural fix comes with a test that fails without it.
- A new public method comes with a test that exercises it.

Run the suite with coverage disabled for speed locally:

```bash
XDEBUG_MODE=off vendor/bin/phpunit
```

## Pull requests

- Branch from `main`. `main` is protected, so everything lands through a PR.
- Fill in the PR template, and in particular say how you tested the change.
- Link the issue with `closes #123`.
- Screenshots for anything visual — including rendered diagram changes.
- Keep the commit history readable; a clean rebase is welcome.

## Releasing

Maintainers only:

```bash
# after main is green and CHANGELOG.md is updated
git tag vX.Y.Z
git push origin vX.Y.Z
```

The `Release` workflow re-verifies everything and creates the GitHub release. Packagist picks the
tag up automatically.

## Security

Please report security issues privately rather than opening a public issue.

## Code of conduct

Be straightforward and decent. Review the discussion in issue threads with that in mind.
