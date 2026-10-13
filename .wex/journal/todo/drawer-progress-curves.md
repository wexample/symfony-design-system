# Step-driven "progress curves" backdrop (from network's tunnel layout)

Opened: 2026-10-09
Updated: 2026-10-09
Author: agent:archeology-drawers

## Read this first — status of this todo

> **This is a proposal for discussion, not an order to code.** It comes from the 2026-10 "bottom of the drawers" archaeology pass. Read it, then discuss it with the owner: every design choice below is to be challenged and validated **before** any code is written. Do not start implementing on your own.
>
> - **Before anything else**: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/PACKAGES/PHP/.wex/local/package-agent-intro.md` — where you stand in the suite, what a package must look like, how to work, and how to judge a drawer todo.
> - Context: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/index.md.j2`, `sources.md.j2`, and the drawers page linked below.
> - Safety: `NETWORK/local/network` runs on production data — read its code only, never run anything against it. Anonymize anything taken from it. Never copy secrets found in its history.

Drawers page: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/drawers-front-ux.md.j2`, section 2.

## What it is

The owner's remembered "SVG animated according to the current tunnel step" (membership subscribe/renew, free payment). It was prototyped in 2022-11 on network's fos branch and never wired to real data: the step count was hard-coded to 3 of 5.

- Behind the tunnel's white floating card, a fixed full-screen SVG (`viewBox 0 0 2500 1500`, `xMinYMin slice`, `z-index -1`) holds big round-capped gradient strokes, all in the lower part of the screen:
  - a 200-unit swoosh along the bottom (orange → pink);
  - a 60-unit wave (blue → green);
  - a 60-unit curl on the left (orange → red);
  - four short 30-unit dashes radiating on the right (orange → pink).
- Each stroke carries a **step range** (`data-curve-start-at` / `data-curve-end-at`).
- On load:
  - strokes whose range starts after the current step stay hidden;
  - strokes already past are fully drawn;
  - the strokes covering the current step appear at the current position, then after 1 s **draw themselves on** to the next step's length (1 s, ease-in-out `cubic-bezier(0.65,0,0.35,1)`).
- So the decoration grows as the visitor advances, and each step change shows a small "reward" animation.

## Sources

- `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/front/class/MovingCurve.ts` (79 lines, the algorithm).
- `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/front/mains/tunnel.html.twig`: the SVG with the 7 paths (exact `d`, widths, colours and ranges).
- `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/front/mains/tunnel/main.scss` (`.svg-curves`, the white card with six layered side shadows) and `main.ts` (the hard-coded `stepsCount = 5`, `stepCurrent = 3`).
- History, in `NETWORK/archeo/repo.git`:
  - `git show 33ce338d8` (first draft, `public/temp.html`);
  - `git show 957799273` and `git show aba08ba23` ("Improve curves", 2022-11-17).
- New-side hooks:
  - `PACKAGES/PHP/packages/wexample/symfony-tunnels/assets/partials/tunnel-navigation.html.twig` (`tunnel_stepper(tunnelCursor)` gives the steps and the current one);
  - this package's `assets/components/stepper/*`;
  - `symfony-tunnels-demo/assets/tunnels/layout.html.twig`.

## What to recover

- Draw-on technique: `strokeDasharray = L`. Position `ratio` maps to `strokeDashoffset = -(ratio·L) - L`, so the stroke grows from the path's end towards its start; reverse the sign to grow from the start.
- The per-stroke step range and the "animate from current to current + 1 on arrival" rule.
- Gradients are created at runtime per stroke, vertical, from two colours given as attributes.
- The composition: the paths and widths, as a default artwork or a demo.

## Proposed modern shape (suggestion)

- A DS component, e.g. `progress-curves` (or a `stepper` variant `backdrop`).
  - Props: `current`, `total` (or the same `steps` array as `stepper`), and an artwork: an SVG partial whose paths carry `data-from`/`data-to` step ranges and colour tokens.
  - Colours come from palette tokens instead of CSS colour names, so they follow the skin and the colour scheme.
- Animate only the delta between the previous step and the current one:
  - the previous step comes from session storage or from a `from` prop given by the tunnel;
  - going back un-draws.
- Respect `prefers-reduced-motion`: draw the final state at once.
- Render it decoratively: `aria-hidden`, no layout impact, works under a modal or panel (`symfony-tunnels-demo` opens tunnels in modals).
- `symfony-tunnels`: an opt-in in its layout that passes `tunnel_stepper(tunnelCursor)` to the component. Other multi-step flows (forms wizard, onboarding `tour`) could reuse it.

## Steps (once validated)

1. Port `MovingCurve` as a DS TS component + Twig template + SCSS. Use the network artwork in the demo page with a "previous / next" control.
2. Unit-test the ratio/offset math and the range rules (hidden / partial / full).
3. Add the opt-in in `symfony-tunnels`. Check the demo tunnel in page, modal and panel modes.

## Open questions

1. Is it a brand flourish of network only (then the artwork stays app-side and only the engine goes into the DS), or a DS feature with a default artwork?
2. Should it animate on every step, or only on completion steps (pay → thanks)?
3. How should unknown future steps (branching tunnels, `unknown` steps in the stepper) map to stroke ranges?
4. Is the white floating card with layered side shadows worth a DS skin token ("soft" skin?), or is it dropped?
