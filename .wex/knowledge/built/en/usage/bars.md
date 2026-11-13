## A bar, what it holds, and which one to reach for

Three things stand along the top of what they act on, and they are not three ways of drawing the same thing. Asking what a control *does* settles which one it is.

| It… | It is… | Drawn by | Says so with |
|---|---|---|---|
| holds controls | a **bar of tools** — a container, nothing of its own to say | `toolbar` (shapes/toolbar) | — |
| picks which of several views is shown | **tabs** | `tab` (components/tab), `page_tabs()` | `role="tab"` / `aria-selected` in place, a link `aria-current` when each is a route |
| switches one tool on or off | a **toggle** | `toolbar--action` or `action-icon`, a `<button>` | `aria-pressed` |
| does something | an **action** | `toolbar--action`, `button` | — |

- **Tabs are never rebuilt with buttons, and a toggle never picks a view.** Two modes of one screen — an editor's *Edit* and *View*, a form's *Simple* and *Advanced* — are two views: tabs. A pin, a full screen, a panel opened beside the page are tools: toggles.
- **A view shown is lit quietly, a tool switched on loudly.** The tab shown is an ink over the bar's ground; a toggle on takes the application's own colour (`--color-selected`). The two are told apart at a glance, and a bar may hold both.
- **Exactly one tab is shown; any number of toggles are on, or none.** Where pressing the one that is on turns it off — the icons of a zone's side bar (`zone--toolbar`), each opening a zone —, these are toggles even though one at most is on in a group.

## Tabs, in the three places they stand

- **On their own** — `tab` / `tabs()` — : a bar of their own, on the shell's ground, a line under it. The views of a block, of a region.
- **Under the header, as the page's navigation** — `page_tabs(items, { back, actions })` — : each tab a route, the way back at its start, what can be done to the thing at its far end. This bar holds actions because it is the page's; it folds into icons then into menus when short of room.
- **In a bar of tools** — a `<tab compact>` inside any `toolbar`, a zone's head (`toolbar toolbar--compact zone--head`) first among them — : the tabs give up their ground and their line and become items of the bar, as tall as its controls. This is what a zone's head showing several views is made of: its tabs at its start, its actions in a `toolbar--group` at its end.

```html
<div class="toolbar toolbar--compact zone--head">
    <tab :items="views" :active-key="view" compact @change="view = $event"></tab>
    <div class="toolbar--group">
        <button type="button" class="toolbar--action" :aria-pressed="String(pinned)" @click="pinned = !pinned">…</button>
        <button type="button" class="toolbar--action" @click="run">…</button>
    </div>
</div>
```

The vue `tab` is a list of tabs picked in place (`role="tablist"`, the arrows moving from one to the next, Home and End to its ends): it emits `change` and shows nothing itself — whoever holds it shows the view, and may wait for what the view needs before moving `active-key`.
