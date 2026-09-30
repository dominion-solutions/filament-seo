---
id: static-analysis
description: Larastan level, and what may never be used to make it pass
applies_to: ["src/**", "tests/**", "phpstan.neon.dist"]
severity: error
---

# Static analysis

Larastan runs at **level 7**. `phpstan.neon.dist` has no `ignoreErrors`, and the repository has no
baseline file. That is deliberate: this package is consumed by type-checked code, so an imprecise
public signature becomes someone else's bug.

## Forbidden

- `@phpstan-ignore`, `@phpstan-ignore-next-line`
- `ignoreErrors` entries in `phpstan.neon.dist`
- a generated baseline
- `@phpstan-var` used to overrule a real type error

If the analyser is right, change the code. If it is wrong, the answer is a precise annotation that
explains why, or a narrower signature.

## Required

- Eloquent relations carry generics: `@return MorphOne<Seo, $this>`.
- Models using `InteractsWithSeo` also `implements HasSeo`.
- Arrays are annotated: `array<string, mixed>`, `list<string>`.
- Fixtures in `tests/Fixtures/` carry `@property` for Eloquent columns.

## Why level 7 and not 9

Level 8 currently passes. Level 9 reports errors originating in `SeoData::make()`, which spreads
`array<string, mixed>` into typed constructor parameters. The honest fix is a builder or explicit
named arguments at the call site — a design decision, not a lint fix. Raise the level when that
lands; do not suppress it to get there.
