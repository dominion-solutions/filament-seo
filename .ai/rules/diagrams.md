---
id: diagrams
description: PlantUML sources are the source of truth; PNGs are generated and committed
applies_to: ["docs/diagrams/**", "docs/assets/diagrams/**", "bin/render-diagrams.sh"]
severity: error
---

# Diagrams

Every diagram in this repository is authored in PlantUML under `docs/diagrams/`. The PNG under
`docs/assets/diagrams/` is **generated**, and both are committed.

GitHub does not render PlantUML in Markdown. The committed PNG is what makes a diagram display, and
the `.puml` is what makes it reviewable as a diff.

## Changing a diagram

1. Edit the `.puml`.
2. `composer diagrams`.
3. Commit **both** the `.puml` and the regenerated `.png`.

Never hand-edit a PNG. Never commit a `.png` without the `.puml` that produced it.

## Verifying

```bash
composer diagrams                    # render
bash bin/render-diagrams.sh --check  # fail if any PNG is stale
```

CI runs `--check`. A stale PNG, a `.puml` that no longer renders, and a `.puml` with no committed
PNG all fail it.

## Pinning

`bin/render-diagrams.sh` pins a PlantUML release and uses the Smétana layout engine
(`-Playout=smetana`) so output is deterministic. Regenerating on another machine with a different
PlantUML or Graphviz would otherwise produce a whole-file diff and make the diagrams unreviewable.

If you change the pinned version, expect every PNG to change, and commit the result in a commit
that says so — not mixed into a content change.
