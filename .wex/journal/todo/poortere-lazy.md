# Poortere: activate heavy components when they come into view, and announce scheme changes

Opened: 2026-10-07
Updated: 2026-10-07
Author: agent:app:main (from symfony-charts)

## Context

The chart demo of the Mojoe design system (`symfony-charts-demo`, `/fr/charts/`) draws nine ECharts charts
on one page, and it lags noticeably when the page opens. The cost is not in the chart: the loader mounts
the components of a page one after the other, `RenderNode::mount()` awaiting each `activateListeners()`
before the next, so every chart is initialised at load, the six below the fold included. A component that
awaits something inside `activateListeners()` holds back every component after it: `chart` hit exactly
that with a fetch, and now launches it without awaiting.

This is not specific to charts. `geo-map` (MapLibre) has the same shape, and so will any component built
around a heavy library. The LDP production planning app (Poortere, for Mojoe) will have dashboards with
several charts, maps and tables.

## Two needs, one per package

1. **Activation on visibility** (`symfony-loader`). A component should be able to say "activate me only
   when I come into view": its html is already in the page, only `activateListeners()` is deferred until
   an `IntersectionObserver` sees the element, and `deactivateListeners()` still runs whether or not it
   was ever activated.
   - Not to be confused with `component_lazy()` / `ComponentLazyLoaderService`, which already exist: they
     fetch the *html* of a component from `/_system/component/render` when its placeholder becomes visible,
     a server round trip. Here the html is server-rendered with the page and only the script waits.
   - The service's observer (`rootMargin: '50px'`) is the model to follow, or to share.
   - Open question: how the component declares it — a static property on the class, an option passed by
     its twig function (`chart(..., { lazy: true })`), or a data attribute the loader reads. A class
     default overridable per call seems right: a chart is lazy by default, a chart at the top of a page
     does not need to be.
   - `RenderNode::afterVisible()` exists but is empty and is called right after mounting, not on
     visibility: either it gets that meaning, or it is renamed.

2. **A colour scheme change event** (`symfony-design-system`). `color-scheme-switch` calls
   `layout.setUsage(AssetUsage.USAGE_COLOR_SCHEME, …)`, which swaps a class on the body and the variables
   with it, and tells no one. A component that reads the variables in script — `chart` for its colours,
   `geo-map` for its pins — has to watch the body's class itself: `chart` sets a `MutationObserver` per
   instance today. An event emitted once the new scheme's stylesheet is applied (through the loader's
   event or mixin system, whichever the stack already uses for such broadcasts) would replace them all.

## Consumers to update once done

- `symfony-charts-ds` `assets/components/chart/chart.ts`: declare itself lazy; replace its
  `schemeObserver` with the event.
- `symfony-geo-ds` `assets/components/geo-map/geo-map.ts`: same, for the map and its pin colour.

## Acceptance

- `/fr/charts/` of the Mojoe design system opens without the lag: only the charts in view are initialised,
  the others when scrolled to.
- Switching the colour scheme redraws the charts in the new colours with no `MutationObserver` left in
  `chart.ts`.

## Done (2026-10-07, agent:main from symfony-design-system)

- `symfony-loader`: `Class/Mixins/LazyActivationMixin.ts` — applied in `init()`, the component's
  `activateListeners()` waits for an `IntersectionObserver` (one shared, `rootMargin: 50px`);
  lazy by default, `lazy: false` in the options activates at once; `deactivateListeners()` takes
  down only what was activated. `RenderNode::afterVisible()` left as it was.
- `symfony-loader`: `AssetsServiceEvents.USAGE_CHANGE` (`usage:change`), said once by the layout
  after `setUsage()` applied a new axis value — every axis, not the colour scheme alone, so
  `theme-select` and an application switching an axis are covered with nothing in the design
  system.
- `symfony-geo-ds` `geo-map.ts`: lazy, and makes its pins and road again in the new accent on
  `usage:change` (it did not follow the scheme before).

## Left

- ~~`symfony-charts-ds` `chart.ts`~~ — done 2026-10-07 (agent:app:main, symfony-charts): the mixin in
  `init()`, `lazy` passed by `chart()` (true unless said), the `MutationObserver` replaced by
  `usage:change`. Seen in a browser driven through DevTools on `/fr/charts/`: 2 charts initialised
  at load in a 900px viewport, 9 once scrolled; switching dark → light redraws them (grid lines
  `rgb(60,60,60)` → `rgb(210,210,210)`). Chrome's `--screenshot` mode never fires the observer: a
  capture of a lazy page has to scroll it through DevTools.
- Not seen in a browser: the Mojoe design system does not build `geo-map`'s script (no build
  file holds it), with or without this change. The mixin was checked by a throwaway test with a
  stand-in observer (seen → activated once; never seen → nothing taken down; `lazy: false`;
  mounted again → waits again).
