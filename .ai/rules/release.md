---
id: release
description: Branching, versioning, and the changelog
applies_to: ["composer.json", "CHANGELOG.md", ".github/workflows/**"]
severity: error
---

# Release

## Branching

`main` is protected. Everything lands through a pull request, and the `Code style` workflow
deliberately does not run its commit-back step on the default branch.

## Versioning

Versions come from git tags, not from `composer.json`. Packagist reads the tag, so the tag alone
decides which versions exist. Do not add a `version` field.

The `seos` table schema is part of the public contract. Any change to it is a major release.

`composer.lock` is gitignored and must stay that way — a library does not ship one. CI resolves
against the lowest and highest dependency sets instead, which is the only way a library's declared
ranges actually get tested.

## Changelog

`CHANGELOG.md` follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/). Every change that
a user can observe gets an entry under `Unreleased`, and a fix is described in terms of what the
user saw, not which method changed.

## Cutting a release

```bash
# main is green, CHANGELOG.md updated, committed, pushed
git tag vX.Y.Z
git push origin vX.Y.Z
```

The `Release` workflow re-runs the full verification and creates the GitHub release. Packagist picks
the tag up automatically. A tag containing `-`, such as `v1.2.0-rc.1`, is marked as a pre-release.

## Dependency bumps

Dependabot opens grouped PRs for minor and patch updates, and leaves major updates ungrouped so they
get read. A bump that breaks CI is usually telling you the constraint in `composer.json` is wrong.
