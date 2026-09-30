---
id: verification
description: What has to pass before a change is described as working
applies_to: ["**"]
severity: error
---

# Verification

`composer check` is the gate. It is `lint` + `analyse` + `test`, and it is exactly what CI runs.

```bash
composer check
```

A change is not "working" because it looks right, and not because the test that was already failing
is still failing the same way. It is working when `composer check` is green.

## While iterating

Full `composer check` on every keystroke is slow. Use the narrow command, then run the full gate
before reporting:

```bash
XDEBUG_MODE=off vendor/bin/phpunit --filter SomeTest
XDEBUG_MODE=off php -d memory_limit=1G vendor/bin/phpstan analyse --no-progress
XDEBUG_MODE=off vendor/bin/pint --test
bash bin/render-diagrams.sh --check
php bin/check-doc-links.php
```

The repository aliases the documentation checks as `composer diagrams-check` and `composer docs`.

## Reporting

State the command and its result. A claim without a green run is not a result.

If a check could not be run, say which and why. Do not describe untested behaviour as working.
