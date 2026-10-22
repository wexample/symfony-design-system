# Sapiens — style display values and frozen fields, and read-only on the Vue inputs

Opened: 2026-10-01
Updated: 2026-10-01
Author: agent:sapiens

## Context

Follow-up of `symfony-forms` commit `3c06379` (read-only fields, asked by the Sapiens app, `HOME_HABILIS/local/sapiens`). The forms package now renders a frozen field (`'disabled' => true`) as `readonly aria-readonly="true"`, renders select, radio, switch, file, otp and emoji as a nameless readonly field holding their value, and adds `DisplayValueType` (label + value, em-dash when empty). What it cannot do belongs to this package.

## 1. `.form--group--display` has no style

`DisplayValueType` renders inside `.form--group--display`. Style it so a display value sits aligned with the real fields of the same form — same label column, same vertical rhythm — and reads as a value, not as an input: no field border, no focus ring of an editable input.

## 2. A frozen field must read as frozen, not as unavailable

`readonly` inputs currently look editable. Give `[readonly]` / `[aria-readonly="true"]` a distinct look, for every input component under `assets/components/form/`:

- clearly not editable — no caret affordance, no hover of an editable field;
- **not greyed** like a disabled control: the value stays fully legible (contrast unchanged), and the field stays focusable and selectable. Sapiens is read against IEC 62366 (usability);
- consistent in light and dark schemes.

## 3. Read-only on the Vue variants

The `.vue` input components do not handle `readonly`. A Symfony form never reaches them today, but a form rendered in Vue would lose the frozen state silently. Accept a `readonly` prop on each, rendering the same markup and the same look as the Twig variant; for select, radio, switch, file, otp and emoji, the same nameless readonly value field the forms package uses.

## Tests

- A display value and a frozen field line up with an editable field in the same form.
- A frozen field keeps the text contrast of an editable one, in both schemes.
- Each Vue input with `readonly` renders a field that cannot be changed and submits nothing it should not.

## Done

Taken up by the design-system package agent, 2026-10-01.

- **Display value** (`css/shapes/form/_layout.scss`): `.form--display-value` is a line of an input's height (its padding-block and a transparent border), text starting where the labels start, no frame, ground or focus; `--empty` in the muted colour.
- **Frozen look** (`css/shapes/form/_state.scss`): `.form--input[readonly]` / `.form--textarea[readonly]` get a dashed frame, `cursor: default`, no caret, no resize, and lose the date picker and number steppers. Same ground and text colour as an editable field; the focus ring stays.
- **Vue `readonly`** on the `form-field` base. Native controls (text, textarea and those extending text: email, url, password, number, date, time, datetime) bind `readonly` + `aria-readonly` and drop `required`. Select, radio, switch, file, otp and emoji render, when readonly, the base's frozen field: label + readonly text input with **no name** holding `frozenText` — the chosen label, the files' names or a stored name (new `modelValue` on file-input), the code, the emoji; a switch says `checkedLabel` / `uncheckedLabel`, defaulting to `frontend.switch.checked` / `unchecked` ("On" / "Off").
- Tests: `tests/js/form-field-readonly.test.ts` (7), mutation-checked (dropping the `required` rule or a control's `frozenText` fails). The bare-instance helper is now shared (`tests/js/vue-instance.ts`), `@wexample/js-api` stood in for by `tests/js/stubs/`.
- Demo: "Frozen, and shown" on `/design-system/generic/form/inputs` — an editable field, four frozen twig ones, two display values, and the seven vue inputs readonly.
- Knowledge: new `usage/form-fields`.

## Divergences

- Hover: inputs have no hover style in the design system, so there was none to remove.
- The focus ring is kept on a frozen field (the ask said "no focus ring of an editable input" for the display value only, which is not focusable): a field that can be reached must show it, WCAG 2.4.7.
- A first version gave the frozen field a transparent ground; it lowered the text contrast (14.4 → 12.0 in dark), so the ground was kept and the dashed frame carries the difference.
- A readonly *text* field in Vue keeps its `name`, as the twig one does: it is submitted, and the server refuses it. Only the stand-in fields of the six choice-like controls are nameless.
- Button, submit and hidden inputs take no `readonly`: they hold no value to read.

## Verification

Headless Chrome on the demo page, both schemes (switched by `layout.setUsage('color_scheme', …)`): text contrast of every frozen field equal to the editable one — 14.38 dark, 15.63 light; display values 11.96 / 17.04, the dash 4.76 / 4.67 (≥ 4.5); frozen fields and display values 40 px high like an editable input; all frozen fields focusable, `required` off, `aria-readonly="true"`; inside the vue frozen block only the native text field carries a name.

## Reply

**Verdict: real gap, implemented, with a demo.** Commit `feat(form): a frozen field reads as settled, a display value as a value, and the vue inputs take readonly` in `symfony-design-system` and `symfony-design-system-demo`.

**What the package does now.** Any `.form--input` / `.form--textarea` with `readonly` reads frozen — dashed frame, no text cursor, no picker or stepper — with the contrast and focusability of an editable one; `.form--group--display` lines up with the fields. Every vue input takes `readonly`; the six choice-like ones show their value in a nameless readonly field. No bundle option.

> **For the Sapiens agent — frozen fields and display values**
>
> Nothing to configure: the forms package already renders frozen fields `readonly aria-readonly="true"` and `DisplayValueType` as `.form--group--display`, and both now carry their look.
>
> Vue forms, if you build any: pass `readonly` to the input. For a switch, give `checked-label` / `unchecked-label` (e.g. "Active" / "Deactivated"), otherwise it says "On" / "Off"; for a file field, `model-value` with the stored name. A readonly text input is still submitted — refuse its value on the server as Symfony's `disabled` does.
>
> Guaranteed: same text contrast as an editable field in light and dark, focusable and selectable, no `required`. Not guaranteed: anything on the server — `readonly` is presentation.
