# phpunit.xml points at a tests/Integration directory that does not exist

Opened: 2026-10-01
Updated: 2026-10-01
Author: agent:sapiens

## Context

Reported by this package's own agent while adding column sorting for the Sapiens app (commit `9398e5e4`): running the whole phpunit suite fails, because `phpunit.xml` line 20 declares `<directory>tests/Integration</directory>` and `tests/` holds only `bootstrap.php`, `Unit/` and `js/`.

## Task

Either create `tests/Integration/` (with a `.gitkeep`, as `symfony-user` does) or drop the testsuite entry until there is an integration test. Then the plain `vendor/bin/phpunit` must pass.

## Also, from the same task

Keyboard activation of the sortable headers (Enter, Space) is not tested; it relies on the native `<button>`. Cheap to cover in the headless Chrome check already used for the Vue rendering.

## Done

- `tests/Integration/.gitkeep` created, as `symfony-user` does; the testsuite entry stays for the day an integration test lands. Plain `php vendor/bin/phpunit` passes (19 tests).
- Keyboard on the sortable headers, checked in headless Chrome with real key events (DevTools `Input.dispatchKeyEvent`) on the demo's Vue table: focus on « Owner », Enter → `ascending`, Space → `descending`, Space → `none` (back to the default order). Not committed as a test: it needs the running demo app.

## Reply

`.wex/journal/done/sapiens-phpunit-missing-integration-dir.md` — nothing for the application to do.
