# `.ai/rules/`

Focused, machine-readable rules for agents working on this repository, one concern per file, each
with YAML frontmatter so a tool can select by `applies_to`.

[AGENTS.md](../../AGENTS.md) is the overview and the place to start;
[CONTRIBUTING.md](../../CONTRIBUTING.md) is the human-facing version. These files hold the mechanical
detail in a form that can be loaded on its own.

| File | Scope | Rule |
| --- | --- | --- |
| [verification.md](verification.md) | all | `composer check` is the gate; no claim without a green run |
| [static-analysis.md](static-analysis.md) | `php`, `phpstan` | Level 7, no baselines, no suppressions |
| [testing.md](testing.md) | `tests/` | Testbench conventions, fixtures as analysis fixtures |
| [diagrams.md](diagrams.md) | `docs/diagrams/**` | PlantUML is the source of truth, PNGs are generated |
| [release.md](release.md) | `composer.json`, `CHANGELOG.md` | Branching, versioning, changelog |

## Adding a rule

Keep each file to one concern. If a rule is about how the code looks, it belongs in `pint.json`; if
it is about what the analyser is allowed to ignore, it belongs in `static-analysis.md`.

When a rule changes, check that [AGENTS.md](../../AGENTS.md) and
[CONTRIBUTING.md](../../CONTRIBUTING.md) still agree. Three copies of one rule is two too many.
