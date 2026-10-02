## What a page tells the layout

A page sets these variables at the top of its template, outside any block; an application layout sets them the same way for all its pages.

| Variable | Values | What it does |
|---|---|---|
| `page_title` | text | The page's title; its translation `@page::page_title` otherwise. |
| `page_show_title` | bool | The title drawn in the page, with the actions of `page_title_actions` beside it. |
| `page_body_mode` | `default`, `wide` | `wide` leaves the reading measure: a table page, a workspace. |
| `page_trail` | `back`, `breadcrumb` | Above the title: the way back to the page above, or the whole trail. Read from the controller's place under `Controller\Pages`. |
| `page_focus` | bool | One thing to do and nothing around it — signing in, a code, terms: no menu, a narrow column under the application's mark. |
| `layout_navigation` | `side`, `top` | Where the application's sections stand (dashboard layout). |

## What stands still and what scrolls

The dashboard layout holds three things still — the header, the page's own navigation (`page_tabs`), the footer — around the one thing that scrolls: the body, on both axes. A page wider than the window (`page_measure: false` under a table of many columns) widens its row to the table and slides under the bars, its scrollbar at the foot of the window; the bars keep the window's width without being told. Nothing of this is measured by a script: put a bar inside the scroller and it will travel with the page.

## Sections in the header

`layout_navigation: 'top'` draws the entries of `page_menu_links` as tabs in the header, after the application's mark (`page_menu_logo`), instead of a menu down the side. It suits an application with a handful of sections, whose side menu would stand mostly empty.

- One bar: the page's title moves into the page (`page_show_title`), with what `page_toolbar_left` and `page_toolbar_right` held beside it, and the way back above it (`page_trail: 'back'` unless the page says otherwise). The footer no longer carries the trail.
- A single section draws no tab: the mark leads home.
- Too narrow for the row, the tabs fold into one button opening them as a list — a native `popover`, no script.
- A menu entry is lit on its own page and on the pages under its address: `/dashboard` stays lit on `/dashboard/patients/12`. `/` is lit on itself only; `active` on the entry overrides.

```twig
{%- extends '@WexampleSymfonyDesignSystemBundle/layouts/dashboard/layout.html.twig' -%}

{%- set layout_navigation = 'top' -%}

{%- block page_menu_links -%}
    {{ menu_item_link('ph:bold/heartbeat', 'Patients', path('dashboard_index')) }}
    {{ menu_item_link('ph:bold/users', 'Users', path('users_index')) }}
{%- endblock -%}
```

## The way back

`breadcrumb_render({ back: true })` draws the page above the current one alone, as « ← its title »; nothing when there is none. The layout draws it above the title with `page_trail: 'back'`, the full trail with `page_trail: 'breadcrumb'`.

## A region beside the page

`page_zones_before` and `page_zones_after` add regions to the left and right of the page's own. With a neighbour, the body fills the window and each region scrolls down on its own; a row wider than the window scrolls sideways as one, its scrollbar at the foot of the window.

`zone--fit-content` makes a region as wide as what it holds — a table of many columns — instead of the room left to it, so the row overflows and the whole row scrolls rather than the table alone:

```twig
{%- block page_zones_after -%}
    <div class="zone zone--panel zone--fit-content">
        <div class="zone zone--scrollable">
            <div class="zone--content">{{ data_table(columns, rows) }}</div>
        </div>
    </div>
{%- endblock -%}
```

## How a zone takes its room

Two questions, answered apart (`css/shapes/_zone.scss`, its last block).

What a zone asks — its own class:

| Class | Asks |
|---|---|
| (none) | what is left, shared with the other plain zones |
| `zone--narrow` | its size (`zone--size-N`), or its content's |
| `zone--measure` | the reading measure, giving way below it |
| `zone--fit-content` | as wide as what it holds |
| resized | the size it was left at (`--zone-size`, written inline by the resizer) |

`--zone-min` (`zone--min-N`) is a floor of its own, under which it is never squeezed.

What its row does when the asks do not fit — the row's class:

| Class | Does |
|---|---|
| (none) | the zones that may give way do, down to their floor |
| `zone--split--scroll` | nothing gives way, the row scrolls sideways |
| `zone--split--floor` | plain zones keep `--zone-floor` (20rem); past it the row scrolls. Any row laid straight in a page's body has it once it holds two zones. |
| `zone--split--wrap` | below their floor, the zones go to the next line |

A row holding a `zone--fit-content` scrolls of itself. Every rule of the block weighs the same, so the order of the file is the order of precedence — the asks, then the rows, which have the last word — and the block closes the file so that no rule written after it overrules a zone's place in its row.
