# Skin axis — 4th theming dimension

## Context

The design system already has three independent CSS-splitting axes:

| Axis | Controls | Examples |
|---|---|---|
| `color_scheme` | Color tokens only | `light`, `dark`, `print` |
| `margins` | Spacing + radius scale | `compact`, `default`, `fat` |
| `animations` | Motion tokens | `none`, `subtle`, `full` |

Shapes are static (not an axis). This 3D model is correct and composable.

## What's missing: `skin`

A **`skin`** axis captures the visual vocabulary that isn't color, spacing, or motion:

- Separator visibility and density
- Shadow depth (flat vs. elevated)
- Border styles (1px solid vs. none)
- Component-level radius tokens (`--radius-interactive`, etc.)
- Surface effects (gradients, textures)

### Proposed values (to refine)

- `default` — functional, separators visible, moderate radius, minimal decoration
- `fluid` — rounded everywhere, fewer separators, richer surfaces

## Why it belongs here and not in color_scheme

Color scheme should stay pure (colors only). Decorative/structural feel is orthogonal.
This gives a free 4D grid: `fluid-dark`, `fluid-light`, `default-dark`, `default-light`, etc.

## On shapes + margin variants

**Do not generate per-margin CSS files for each shape.** Instead:

- Shapes reference tokens (`var(--space-button-y)`, `var(--radius-interactive)`)
- The `margins` (or `skin`) axis redefines those tokens
- The shape adapts without extra files

Only create a shape variant file when the **structural behavior** changes
(e.g. stacked icon+label in fat mode) — not just for sizing.

## Implementation sketch

Each component gets optional skin variant files, same pattern as color_scheme:

```
button.skin.fluid.css
card.skin.fluid.css
layout.skin.fluid.css
```

Loaded by the existing asset usage system, same mechanism as `color_scheme`.

The `skin` axis is registered in bundle config alongside `margins` and `animations`.

## Immediate use case

Strip all decorative rules from the current `light`/`dark` files into a `skin: default` file.
Create `skin: fluid` for the "sexier" variant: rounded everywhere, no separators, richer feel.
Both work in light AND dark independently.
