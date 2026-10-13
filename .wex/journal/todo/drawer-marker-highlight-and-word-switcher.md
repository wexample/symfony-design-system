# Animated marker highlight and slot-machine word switcher (wexample.com hero, 2022)

Opened: 2026-10-09
Updated: 2026-10-09
Author: agent:archeology-drawers

## Read this first — status of this todo

> **This is a proposal for discussion, not an order to code.** It comes from the 2026-10 "bottom of the drawers" archaeology pass. Read it, then discuss it with the owner: every design choice below is to be challenged and validated **before** any code is written. Do not start implementing on your own.
>
> - **Before anything else**: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/PACKAGES/PHP/.wex/local/package-agent-intro.md` — where you stand in the suite, what a package must look like, how to work, and how to judge a drawer todo.
> - Context: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/index.md.j2`, `sources.md.j2`, and the drawers page linked below.
> - Safety: `NETWORK/local/network` runs on production data — read its code only, never run anything against it. Anonymize anything taken from it. Never copy secrets found in its history.

Drawers page: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/drawers-outside.md.j2`

## What it is

A one-page landing for wexample.com (GitLab `wexample/blog-v2`, never part of network) with two small text effects that exist nowhere in network or the packages: words highlighted by a **marker stroke that draws itself**, and a **word that rolls like a slot machine** before settling on the next adjective.

## Where

`/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/outside/wexample_blog-v2/project/` (read-only clone): `index.html`, `assets/styles.scss`, `assets/script.ts`, `assets/images/hexagon.svg`. Commits `664c88b` "First commit" and `9d53150` "Prod fixes", 2022-04-27.

## Look and behaviour

- **Hero**: 900 px centred column; header with the wordmark left and a black rounded button "Je suis freelance" (Fredoka One) right. Behind the text a big rounded hexagon (20 rem, rotated 16°, opacity 0.1, offset -5 rem). Slogan (Fredoka One 3 rem): "Le réseau qui vous apporte des solutions web **sur-mesure** et **durables**", then "Wexample, le réseau de freelances {rolling word}", then "Vous allez adorer travailler avec nous."
- **Marker highlight** (`.highlight`): a pseudo-element pill behind the word (top 0.8 rem, left 1 rem, right -0.5 rem, bottom 0; fully rounded; rotated -3°), so it covers the lower part of the letters like a felt pen. It grows **from the centre outwards** (keyframes `slidein`: left/right from 50 % to their final values) in 1 s with `cubic-bezier(0.070, 1.120, 0.025, 0.975)` — a slight overshoot. The text gets a white offset shadow `0.15rem 0.25rem 0 white` so it stays readable over the colour. Colours: blue `#32ace1` (class wrongly named `highlight-orange`) and green `#56a956`. Elements start as `.highlight-delayed`; JS turns them on one by one, first at 500 ms, then every 200 ms.
- **Word switcher** (`.word-switch`): every 2 s, the span shows a random 8-character base-36 string every 10 ms, 50 times (~0.5 s), then the next word (inclusif, solidaire, généreux, structuré, organisé, expérimenté). While rolling it gets `.rolling` (width 5 rem, 1 rem side margins) with a springy `cubic-bezier(0.135, 1.575, 0.025, 0.975)`, then eases back with `cubic-bezier(0.075, 0.820, 0.025, 0.975)`.

## Proposed shape (suggestion)

- DS: a `highlight` text decoration (colour via a CSS variable, optional staggered reveal on visibility, `prefers-reduced-motion` = static) and a `word-switcher` component (list of words, interval, scramble on/off, `aria-live="polite"` with the final word only).
- The hero itself (texts, hexagon) belongs to the network 2027 public home, not to the DS.

## Open questions

1. Wanted in the DS, or only in the network public pages?
2. Keep the scramble effect (accessibility: screen readers must not read random strings)?
