## Generated avatars

A face for whoever has no picture, drawn by an engine from a seed and served as an SVG an `img` shows — in a page, a vue component or a mail alike.

```twig
{{ avatar(user.id, { label: user.displayName }) }}
{{ avatar(user.id, { engine: 'wexies-2020', size: '4rem' }) }}
<img src="{{ avatar_url(user.id) }}" alt="">
```

- **The seed** is what does not change — an id. Never the name: a face that changes with a rename is someone else's.
- **`label`** is the name the face stands for, for an engine that writes something of it (`initials`), and the image's `alt` unless `alt` is given.
- **`engine`** defaults to `wexample_symfony_design_system.avatar.default_engine` (`initials`).
- The address, `/_design-system/avatar/{engine}/{seed}.svg?label=`, always answers the same face, and is cached for a year. Its route, `wexample_design_system_avatar`, is exposed to the front.

## A group of faces

The people of something — who is in a project, on a task, in a session — as a pile of small faces: `avatar-group`, in twig and in vue. The first ones overlap by a quarter, each ringed with the ground it stands on (`--avatar-group-ring`: a zone's surface, a block's inside a block); the rest are counted as one more face (`+3`); each face is named under the pointer.

```twig
{{ component(render_pass, '@WexampleSymfonyDesignSystemBundle/components/avatar-group', {
    items: members|map(m => { label: m.name, seed: m.id }),
    max: 4,
}) }}
```

- **`items`**: `{ label, seed?, src? }` — a picture (`src`), a face drawn from a seed (`avatar()`, twig), or the label's initials on a neutral ground. In vue, a drawn face comes as its `src` (`avatar_url()`, handed by the server).
- **`max`** (4): the faces at most, the count among them — six people at four shows three faces and `+3`.
- **`size`** (`1.5rem`): the faces' `--avatar-size`.
- The rule is written once on each face: `AvatarGroupHelper` (PHP and TS), `split()` and `initials()`.

## Engines

An engine implements `Interface/AvatarEngineInterface`: a name, and `render(seed, label)` returning a square SVG with a viewBox and no size. Any service implementing it is registered — the design system ships `initials` (`Avatar/InitialsAvatarEngine`), `symfony-design-system-effects` ships `wexies-2020`, an application adds its own. `Service/AvatarService` holds them by name.

What an engine derives from its seed goes through `Helper/AvatarHelper`: `avatarPick(seed, word, count)` hashes the seed with a word of its own, so two choices of a face do not move together; `avatarColor(seed)` gives a hue with a lightness white stands out on.
