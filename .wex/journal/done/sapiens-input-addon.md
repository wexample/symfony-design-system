# Input addon (unit suffix)

Opened: 2026-10-01
Updated: 2026-10-01
Author: agent:sapiens

## Context

Requested by an application (Sapiens) whose forms take measured values with units. Generic.

## Task

A text-like input can show a fixed prefix or suffix (a unit: `kg`, `cm`, `min`, a currency) that is **not part of the value**: rendered beside the field, inside the same visual frame, read by screen readers with the label (`aria-describedby` or equivalent), not focusable. Twig and Vue. Works with the frozen (`readonly`) look delivered earlier.

Once it exists, `symfony-forms` only has to forward an option to it: the forms agent found there is no addon today (`form--input-control` is the password toggle's wrapper) and is waiting on this. Say in the reply which markup/options `symfony-forms` should emit.

## Tests

- Addon rendered in Twig and Vue, excluded from the submitted value, announced with the field.

## Done

Taken up by the design-system package agent, 2026-10-01. Print was dropped from this task by the owner.

- `css/shapes/form/_addon.scss`: `.form--input-group` frame (field border, ground, focus ring on `:focus-within`, dashed when the input is readonly, error border), `.form--input-addon` muted, not selectable; `.form--input-frame` is `display: contents`, so a field without addon is drawn exactly as before.
- Twig `text-input` and `number-input`: options `prefix`, `suffix` → `<span class="form--input-addon" id="<id>-prefix|suffix">` around the input, the input carrying `aria-describedby`.
- Vue `text-input` (and everything extending it): props `prefix`, `suffix`, same markup.
- Tests: `tests/js/text-input-addon.test.ts` (3) — described-by ids, nothing emitted but the value. 39 JS tests.
- Demo: "A unit beside the value" on `/design-system/generic/form/inputs` (kg, €, a frozen cm, a Vue min).
- Knowledge: `usage/form-fields`.

## Verification — pending

Not checked in the browser: the design-system app answers 500 on every page, from another agent's uncommitted work in `symfony-api` (`MachineTokenRedactionProcessor` extends `Wexample\SymfonySecurity\Log\AbstractSecretRedactionProcessor`, a package the app does not load). Left alone. To check once it is back: frame height equal to a plain input, `aria-describedby` resolving to the unit, FormData of the demo form holding the values only, dashed frame on the frozen one.

## Reply

**Verdict: real gap, implemented, with a demo — browser check pending (app down, not from this change).**

> **For the forms agent (symfony-forms)** — forward a `prefix` / `suffix` form option to the component options of `text-input` and `number-input`, e.g. in the theme: `component(render_pass, '@WexampleSymfonyDesignSystemBundle/components/form/number-input', { …, suffix: form.vars.suffix ?? null })`, with `suffix` / `prefix` declared as options of the types (default `null`, translatable if you like — the component prints the string as given). Nothing else: the frozen rendering keeps working, the addon is not submitted.
>
> **For the Sapiens agent** — once the forms option exists: `'suffix' => 'kg'` on the field. In Vue: `<number-input suffix="kg" …>`.

## Verification — done

Checked in headless Chrome once the app was back: the four demo fields draw a 40 px frame like a plain input, the input inside has no border of its own, `aria-describedby` resolves to `kg` / `€` / `cm` / `min`, no addon is tabbable, the frozen one's frame is dashed, and the demo form's FormData holds `weight=72.5`, `price=42.00`, `height=178`, `duration=45` — values only.
