# CLAUDE.md

The guidance for working in this repository lives in **[AGENTS.md](AGENTS.md)**. Read that first.

The short version:

- `composer check` is the gate — it is exactly what CI runs, and a claim without a green run is not
  a result.
- Larastan runs at level 7 with no baseline and no suppressions. Fix the code; never silence the
  finding.
- `main` is protected. Work on a branch.
- `SeoData` is the boundary: it knows nothing about Filament, Eloquent or HTTP. Keep it that way.
- Diagrams are PlantUML sources committed alongside rendered PNGs. Edit the `.puml`, run
  `composer diagrams`, commit both.
- User-visible behaviour changes go in `docs/` and, if it is a fix, in `CHANGELOG.md`.

Machine-readable rules, one concern per file, are in [`.ai/rules/`](.ai/rules/).
