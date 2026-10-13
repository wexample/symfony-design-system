# Shape-filling spinner (network's logo "ripple" loader)

Opened: 2026-10-09
Updated: 2026-10-09
Author: agent:archeology-drawers

## Read this first — status of this todo

> **This is a proposal for discussion, not an order to code.** It comes from the 2026-10 "bottom of the drawers" archaeology pass. Read it, then discuss it with the owner: every design choice below is to be challenged and validated **before** any code is written. Do not start implementing on your own.
>
> - **Before anything else**: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/PACKAGES/PHP/.wex/local/package-agent-intro.md` — where you stand in the suite, what a package must look like, how to work, and how to judge a drawer todo.
> - Context: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/index.md.j2`, `sources.md.j2`, and the drawers page linked below.
> - Safety: `NETWORK/local/network` runs on production data — read its code only, never run anything against it. Anonymize anything taken from it. Never copy secrets found in its history.

Drawers page: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/drawers-front-ux.md.j2`, section 3. Also listed in `already-extracted-check.md.j2` as "optional DS spinner variant".

## What it is

network's global loading indicator (fos branch, 2022-11) is a 150 px wex hexagon logo that fills up with concentric red bands. Each band draws around its circle at its own speed, while the white "W" stays cut out.
- Five circles centred top-right of the logo, radii 30–270, stroke 4.2 rem, round caps.
- Clipped by the hexagon outline, masked by the W glyph.
- Colours and durations: pale pink `#eec9c8` (2 s), `#a12a26` (1.5 s), `#8a1b17` (0.5 s), `#660D0A` (1 s), `rgb(161,42,38)` (1 s), linear timing.
- Played once when the spinner container becomes visible (MutationObserver).

## Sources

- `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/front/components/main-spinner.{html.twig,scss,ts}`.
- `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/front/class/ProgressPath.ts` (clean; `ProgessPath.ts` is the typo'd copy actually imported).
- Logo paths: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/assets/images/wex-logo.svg`.
- Container: `…/src/Wex/BaseBundle/Resources/templates/bases/html/default.html.twig` (`#main-spinner`, block `main_spinner_inner`), `…/css/partials/_global.scss`.
- History: `git show 9a8ad2f13` ("Overlay test", `public/temp.html`), `git show 24b07f855` ("First spinner", 2022-11-28). Spec: GitLab issue #205 (`NETWORK/archeo/gitlab/issues/205.md`).
- Current DS spinner: `assets/components/spinner/*` (a single circle, sizes, variants, `role=status`).

## What to recover

- The technique: N concentric stroked circles, each "drawn on" via `stroke-dasharray`/`stroke-dashoffset` with its own duration, inside a `clipPath` (the silhouette) and a `mask` (an optional cutout).
- Bugs to avoid:
  - the circles are duplicated (10 elements for 5);
  - a new `ProgressPath` instance is created on every show;
  - it plays only once, with no loop for long waits.

## Proposed modern shape (suggestion)

- A DS spinner variant (e.g. `spinner--shape`).
  - Inputs: a silhouette path (clip), an optional cutout path (mask), a centre point and a list of band colours (palette tokens by default).
  - Behaviour: CSS-only keyframes on `stroke-dashoffset`, looping with fill → hold → fade, staggered durations.
  - `prefers-reduced-motion`: a static filled shape with a subtle opacity pulse.
  - Unique ids per instance for clip and mask.
- network 2027 passes the wex hexagon + W. The DS default could stay the plain circle.

## Steps (once validated)

1. Template + SCSS variant, demo page entry with a sample silhouette and the wex logo (if the owner allows the brand in the demo).
2. Check it in the DS overlay / modal loading contexts and in the dark colour scheme.

## Open questions

1. Is a brand-shaped loader wanted in network 2027 at all, or is the DS circle enough?
2. Loop or one-shot? The original was one-shot, which looks frozen on long loads.
3. Should the same "ripple fill" be reused as a progress indicator (fill ratio = progress) instead of a spinner?
