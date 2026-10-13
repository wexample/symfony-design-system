# Generated "wexie" avatar (seeded colour + eyes glyph)

Opened: 2026-10-09
Updated: 2026-10-09
Author: agent:archeology-drawers

## Read this first — status of this todo

> **This is a proposal for discussion, not an order to code.** It comes from the 2026-10 "bottom of the drawers" archaeology pass. Read it, then discuss it with the owner: every design choice below is to be challenged and validated **before** any code is written. Do not start implementing on your own.
>
> - **Before anything else**: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/PACKAGES/PHP/.wex/local/package-agent-intro.md` — where you stand in the suite, what a package must look like, how to work, and how to judge a drawer todo.
> - Context: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/index.md.j2`, `sources.md.j2`, and the drawers page linked below.
> - Safety: `NETWORK/local/network` runs on production data — read its code only, never run anything against it. Anonymize anything taken from it. Never copy secrets found in its history.

Drawers page: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/drawers-front-ux.md.j2`, section 1.

## What it is

Since 2019, a network member with no uploaded picture gets a generated face:
- a coloured disc with a glossy white sheen (a diagonal gradient from transparent to 40 % white, top-left);
- one of 26 hand-drawn pairs of white eyes (`a`…`z`): happy arcs, crosses, hearts, a cyclops, a monocle, sleepy lids… Each pair sits at a different spot in the face, so the face seems to look somewhere.

The eyes are chosen from a letter derived from the name, the colour from a seed derived from the name. It is network's member identity ("wexies") and is still live in prod. It has been broken since 2021-10: the colour became random on every request (Rector replaced `mt_rand` by `random_int`, which ignores `mt_srand`).

The design system today has a round `.avatar` frame for "a portrait, an icon or initials" (`assets/css/shapes/_avatar.scss`), and `symfony-user-ds` shows `user.initials` in the account menu. Nothing generates a face.

## Sources

- Template: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/front/partials/user-picture.svg.twig` (identical in `NETWORK/archeo/trees/develop-131-fos-user/front/partials/`).
- The 26 eyes (the set to keep): `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/front/partials/eyes/{a..z}.svg`. Each file has 2 white paths; the viewBox origin varies on purpose.
- Older, dead set (do not use): `NETWORK/local/network/public/images/svg/eyes/*.svg`.
- Generation: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/src/Controller/Entity/UserController.php`, `pictureView()`, lines about 89–145.
- Helpers: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/PACKAGES/PHP/packages/wexample/php-helpers/src/Helper/TextHelper.php` (`getLetterFromString`, `getNumberHashFromString`, lines 110–127).
- History, in `NETWORK/archeo/repo.git`:
  - `git show 0e8bc5528` (2019-07, CSS version with the 26-colour Flat-UI palette `$userColors`);
  - `git show a1b66e97a` (2019-11, first SVG; the corner radius varied with the name);
  - `git show c5c7480df` (2019-12, seeded shuffle);
  - `git show 99091eb32` (2021-10, the `random_int` regression).
- Existing DS pieces: `assets/css/shapes/_avatar.scss`, `assets/components/button-menu/*` (option `avatar`), `src/Twig/BarExtension.php` (option `avatar`), `PACKAGES/PHP/packages/wexample/symfony-user-ds/assets/partials/user_menu.html.twig`.

## What to recover

- The 26 eyes SVG fragments: the owner's own drawings. Normalise them to one viewBox while keeping each eye's offset.
- The look: a full disc, a flat seeded colour, the sheen gradient (`x1 74% y1 100% → x2 26% y2 0%`, white opacity 0 → 0.4), white glyph.
- The idea that **a seed string → (glyph, colour) is deterministic**.

Do **not** port:
- `str_shuffle` + `mt_srand` (it depends on the PHP RNG implementation);
- `random_int`;
- the `firstName`/`lastName` coupling, which changes the face on rename.

## Proposed modern shape (suggestion)

- A DS `avatar` variant `generated`.
  - Input: a seed (string, e.g. a stable user id or uuid, not the display name) and an optional size.
  - Output: inline SVG inside `.avatar`.
- Seed → `hash = crc32` or the first bytes of `sha1`:
  - glyph index = `hash % count(glyphs)`;
  - hue = another slice of the hash.
  - The colour is built in OKLCH/HSL with fixed lightness and chroma, so the white eyes always contrast. Alternatively, pick from a named palette (the 2019 `$userColors` idea) or from DS palette tokens, so it follows the colour scheme.
- Glyph sets are pluggable (a directory of SVG fragments). The DS ships a neutral default (initials?), and the wex eyes are registered by the app. Alternatively, the wex eyes become the DS default if the owner wants the brand in the DS.
- Rendering:
  - server-side Twig (`avatar_generated(seed)`) for pages and mails (as PNG? mails cannot show inline SVG reliably);
  - a JS twin with the same hash for Vue lists.
  - Unique ids per instance (`lgrad` collides when inlined).
- `symfony-user-ds`: use it as the fallback when a user has no picture (account menu, user cards). `symfony-media` keeps uploaded pictures.

## Steps (once validated)

1. Decide the seed, the colour rule and where the glyph set lives.
2. Normalise the 26 SVGs into fragments. Add a demo page in `symfony-design-system-demo` showing all 26 and a grid of seeded avatars.
3. Twig function + component + JS helper with identical hashing. Test: same seed → same output in PHP and JS.
4. Wire the fallback in `symfony-user-ds`.

## Open questions

1. Should the eyes ship in the DS (brand inside a generic package) or stay app-side and be registered as a glyph set?
2. Colour: free hue with fixed lightness, or a named palette? Should the 2019 "shape varies with the name" (square → circle) come back?
3. Seed: user id/uuid (stable) or username (portable across apps)?
4. Animated version (#119 asked for animated profile pictures): eyes blinking or looking at the cursor would be cheap with SVG. Wanted?
5. Mails: is a server-rendered PNG needed?
