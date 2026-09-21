# symfony_design_system

Version: 16.0.1

A Symfony bundle that ships a ready-made design system for web applications: Twig components (buttons, modals, toasts, forms, entity bars), SCSS layouts (`dashboard` and `default`), Vue mixins, and a suite of Twig extensions that wire them together. Every page flows through a `RenderPass` object managed by `AbstractDesignSystemController`, which handles template resolution, per-layout asset loading, and render-node–scoped translations. It targets Symfony developers who want consistent UI primitives and a structured front-end pipeline without building one from scratch.

## Table of Contents

- [Architecture](#architecture)
- [Integration in the Suite](#integration-in-the-suite)
- [Dependencies](#dependencies)
- [Versioning & Compatibility Policy](#versioning--compatibility-policy)
- [License](#license)
- [About us](#about-us)
- [Migration Notes](#migration-notes)

## Architecture

The bundle is a Symfony library (`wexample/symfony-design-system`) that adds a ready-made design system on top of `wexample/symfony-loader`. It ships PHP services (Twig extensions, controllers), layout templates, component triads (Twig + TypeScript + SCSS), and shared CSS and Vue primitives. Nothing here is an application; every piece is meant to be extended or overridden by the host app.

### Bundle registration and service wiring

src/WexampleSymfonyDesignSystemBundle.php extends `AbstractBundle` and implements `LoaderBundleInterface`. Its only runtime responsibility is to tell the loader where the bundle's front-end assets live:

```php
public static function getLoaderFrontPaths(): array
{
    return [
        BundleHelper::getBundleCssAlias(static::class) => __DIR__ . '/../assets/',
    ];
}
```

src/DependencyInjection/WexampleSymfonyDesignSystemExtension.php calls `loadConfig()` (which reads src/Resources/config/services.yaml) and then merges two layout bases — `modal` and `panel` — into the loader's parameter `wexample_symfony_loader.layout_bases`. That parameter tells the loader which component to use as the container when a page is embedded inside an overlay.

`services.yaml` registers every class under `Controller\`, `Form\`, `Service\`, and `Twig\` with autowiring and autoconfigure. `AppExtension` is excluded from the wildcard scan and registered separately so its `$appHomeRoute` constructor argument can be injected from the `wexample_ds_app_home_route` parameter.

### Twig layer

All Twig extensions extend src/Twig/AbstractTemplateExtension.php, which wraps `twig->render()` in `renderTemplate()` and declares `TEMPLATE_FUNCTION_OPTIONS` (`is_safe: html`, `needs_environment: true`). Each extension owns one visual concern:

| Extension | Functions registered |
|---|---|
| src/Twig/AppExtension.php | `app_home_url()` — resolves `wexample_ds_app_home_route`, returns `#` when absent or unroutable |
| src/Twig/BreadcrumbExtension.php | `breadcrumb()`, `breadcrumb_render()`, `breadcrumb_append_route()`, `breadcrumb_stack()` — trail is accumulated in `Request::attributes` under `_breadcrumb_stack` |
| src/Twig/ButtonExtension.php | `button()`, `button_menu()`, `button_link()`, `button_target()` — delegate to the loader's `ComponentsExtension::component()` |
| src/Twig/DocumentExtension.php | `document_embed($src, $title, $options)` — an `iframe` inside a `.media` box; option `ratio` picks the modifier, `media--fill` otherwise. The `$title` is positional because an untitled iframe is an accessibility failure |
| src/Twig/EntityExtension.php | `entity($renderPass, $entity, $format)` — resolves `@front/components/entity/{snake_name}/{format}` via `ComponentsExtension` |
| src/Twig/FormExtension.php | `form_submit()` — renders `components/button/button.html.twig` with `type: submit` injected |
| src/Twig/ImageExtension.php | `content_image()` — renders `components/content-image/content-image.html.twig` with `loading: lazy` as default |
| src/Twig/MenuExtension.php | `menu_item()`, `menu_items()`, `menu_separator()`, `menu_item_link()`, `menu_item_collapsible()`, `menu_item_collapsible_from_controller()` |
| src/Twig/MessageExtension.php | `message_info()`, `message_success()`, `message_warning()`, `message_error()` — all render `components/message/message.html.twig` with a type and a default icon |
| src/Twig/PropertiesExtension.php | `properties($items, $options)` — key/value list, options `bordered`, `split`, `compact`, `stacked` map to `properties--*` modifiers |
| src/Twig/TabExtension.php | `tab_item()`, `tab_item_link()` — render `components/tab-item/tab-item.html.twig` |
| src/Twig/TableExtension.php | `data_table($columns, $rows, $options)` — normalizes the column definitions, renders `components/data-table/data-table.html.twig` |
| src/Twig/UiStateExtension.php | `ui_state_get($key, $default)` — reads from `session['ui_state.{key}']` |

`button_target($icon, $label, $href, $target, $options)` takes the same first three arguments as `button_link()`, plus where the page it points at is loaded: `modal`, `panel`, or the name of an embed the page holds. It merges `href` and `target` into `$options` and renders `components/button-target`. The class list is the caller's — `options.class` replaces it entirely, defaulting to `button` — because the same behaviour has to sit on a `.button` and on a `.table--icon-link`.

### A menu the pages fill

A menu written by hand is a list someone has to edit when a page appears, and a page shipped by a bundle has nobody to edit it — the application owning the template has never heard of it. src/Attribute/MenuItem.php turns that around: a controller action declares which run of items it joins, and the menu asks for the run.

```php
#[MenuItem(group: 'app_application', weight: 10)]
#[Route('/app/{id}/blog/stats', name: 'acme_blog_board_stats')]
public function index(App $managedApp): Response
```

The attribute carries a group and a weight and nothing else, because everything else is already known elsewhere: `menu_item` reads the label and the icon from the page's own translations — `page_title` and `page_icon` — and decides on its own whether it is the page being read. Repeating any of it on the attribute would be a second place to keep in step with the first.

src/Service/MenuItemRegistry.php walks `RouterInterface::getRouteCollection()`, reflects on each controller through `RouteHelper::resolveMethodReflection()`, and sorts each group by weight then route name — the order bundles are discovered in is the order they were installed in, which is to say no order at all. The walk is held for the request and nothing is written to disk: the collection it reads from is itself compiled and cached, so a cache here would only add a second thing to invalidate when a bundle arrives.

`menu_items($group, $routeParams, $options)` renders a whole run, one `menu-item` per route, and returns an empty string for a group nobody joined. That is what lets a template put a heading above a run only when there is a run:

```twig
{%- set application_items = menu_items('app_application', app_params) -%}
{%- if application_items -%}
    {{ menu_separator('@layout::menu.application') }}
    {{ application_items | raw }}
{%- endif -%}
```

`| raw` is not optional there: twig escapes what it no longer knows came from a renderer once it has been through a variable.

`menu_item_collapsible_from_controller()` builds the submenu automatically: it scans `RouterInterface::getRouteCollection()` for routes whose `_controller` class path shares a namespace prefix with the given controller namespace, keeps only the top-level or index routes (using `ClassHelper`), then compares each against the current request route to decide whether the group is open.

### Double rendering

An element of the design system is rendered on the server by Twig, or in the browser by Vue, and the choice belongs to the page, not to the element. So an element that a Vue-rendered page may need exists **twice**: a Twig triad and a Vue twin, emitting the same markup and taking the same options.

This is not duplication for its own sake. A Twig component binds its behaviour through the loader's `.com-init` placeholder, which resolves the element from the previous DOM node — a mechanism that cannot reach markup Vue produced. A table drawn by `data-table.vue` therefore cannot host `components/button-target.html.twig`, however much both would like it to.

The rule that keeps the pair honest:

- Twin files say so, at the top, both ways: *"Client-side twin of X: same options, same markup. A change to either is a change to both."*
- The markup is identical, class for class. Where Vue needs an extra element for `v-html` or a `v-if`, the twin is restructured until the output matches.
- The behaviour is shared by a module both import — assets/js/Helper/TargetHelper.ts for the target buttons — so a rule about *what happens* lives in one place even when *what is drawn* lives in two.
- Options carry the same names on both sides. Where the language forces a difference, it is the mechanical one only: `class` becomes `className`, snake_case Twig option keys become camelCase Vue props.

The principle is being deployed progressively; the elements that already have their twin:

| Element | Server | Client |
|---|---|---|
| Table | `data_table()` from src/Twig/TableExtension.php, assets/components/data-table/data-table.html.twig | `<data-table>`, assets/components/data-table/data-table.vue.twig |
| Target button | `button_target()` from src/Twig/ButtonExtension.php, assets/components/button-target/button-target.html.twig | `<button-target>`, assets/components/button-target/button-target.vue.twig |
| Spinner | assets/components/spinner/spinner.html.twig | assets/components/spinner/spinner.vue.twig |

#### Tables: where the twins part company

The table pair is the oldest and the one with the most divergence. It is unavoidable, and worth knowing before trying to unify them:

- The Vue side accepts functions for `format`, `href`, `icon` and `params`, and resolves routes through the `routing` service. Twig has no closures and the URLs are already known at render time, so the Twig side takes the computed result in the row: a `link` cell reads `{ label, href }`, an `icon` cell `{ icon, href }`, an `actions` cell a list of `{ icon, href }`.
- `secondary` is applied to header and body cells by the Vue component, to body cells only by the partial.
- The Vue component has a refreshing state — rows dimmed under an overlay while new ones load — which a server-rendered table cannot have. The Twig `loading` option only replaces the body with the spinner row.

Shared column options: `key`, `label`, `align`, `secondary`, `class` (`className` in Vue), and `cell` for the cell kind — `text`, `html`, `icon`, `link`, `actions`. An `actions` cell entry may carry `target` and `target_options` (`targetOptions` in Vue), which both sides hand to the target button.

### Controllers

src/Controller/UiStateController.php exposes `POST /_ui-state/set`. It reads `{key, value}` from the JSON body and writes `session['ui_state.{key}'] = value`. This is the default persistence target for menu-collapse state; apps that need server-side persistence override `App::persistUiState` on the JS side instead of calling this endpoint.

src/Controller/FixturesController.php serves `placeholder.svg` from `assets/fixtures/` at the design-system base route.

src/Traits/SymfonyDesignSystemBundleClassTrait.php returns `WexampleSymfonyDesignSystemBundle::class` as the bundle class name. A controller mixing it in tells the loader which bundle to resolve template namespaces and translation paths against; the preview controllers that do so live in the `symfony-design-system-demo` package, not here.

### Helper

src/Helper/EntityDisplay.php is a value-object holding the three standard entity display identifiers (`bar`, `card`, `list-item`) that the `entity()` Twig function uses to resolve component paths. Custom formats beyond these are valid; the class documents the standard set rather than enforcing it.

### Asset layout

Assets live in `assets/` and are divided into five directories.

**`layouts/`** defines the two provided HTML layouts. assets/layouts/default/layout.html.twig extends the loader's base layout and adds the `page_body_container` block (wraps the body in a container with an optional `h1`). assets/layouts/dashboard/layout.html.twig extends `default` and renders a two-sidebar shell: a left `menu-collapsible-panel`, a scrollable main content column, and a right `menu-collapsible-panel`. The left panel reads its initial collapsed state from `ui_state_get('ui.layout.menu.left', true)`. Host apps extend `dashboard/layout.html.twig` to inject `page_menu_links` and other blocks.

**`components/`** holds interactive units. Each component is a triad: a `.html.twig` template rendered server-side, a `.ts` file that attaches client behaviour, and a `.scss` file for component-scoped styles. Notable components:

- `modal` and `panel` both extend `AbstractOverlayPageManager` (assets/js/Class/AbstractOverlayPageManager.ts), which layers `FadeAnimationMixin`, `FocusableComponentMixin`, and `OverlayMixin` from the loader. It handles open/close with optional confirm-on-close and dirty-form detection.
- `button-target` (assets/components/button-target/button-target.ts) intercepts clicks on its anchor, reads `data-target` and `data-target-options`, and hands the href to `loadIntoTarget()` from assets/js/Helper/TargetHelper.ts, which dispatches to `ModalService`, `PanelService` or `EmbedService`. The two overlays keep their place in URL hash params, so a persistent one reopens on reload; an embed does not, belonging to the page that placed it. A modified click is left to the browser, so every target stays openable as a full page.
- `menu-collapsible-panel` (assets/components/menu-collapsible-panel/menu-collapsible-panel.ts) toggles `gutters--collapsible--collapsed` on its element and calls `app.onMenuStateChange(menuId, open)` on every toggle, which is the point where the host app or the default `UiStateController` persist the state to the session.
- `toast` applies `FadeAnimationMixin`, `AutoCloseMixin`, and `ActionLinksMixin`; it self-removes after 4 s unless `sticky` is set.

**`js/`** contains shared TypeScript classes and Vue mixins.

- `AbstractCollapsibleComponent` (assets/js/Class/AbstractCollapsibleComponent.ts) listens for a click on a selector returned by `getToggleSelector()` and toggles `is-open` on the element.
- `AbstractDesignSystemVueMixin` (assets/js/Vue/AbstractDesignSystemVueMixin.ts) adds `waitForAppReady()` to any Vue component that must wait for the JS app to finish bootstrapping before performing async work.

**`components/`** holds one directory per component, named after it, containing every renderer that component has: `.html.twig`, `.scss`, `.ts`, `.vue`, `.vue.twig`. `bases/entity/entity.vue` and `bases/form/form.vue` are the root Vue components for entity and form contexts; the input components under `components/form/` map one-to-one to the Symfony form types declared in `src/Form/Demo/`.

**`css/`** is what is *not* a component: `mixins/` (SCSS mixins for layout, spacing, typography, overlays), `shapes/` (one stylesheet per shape), `partials/` (palette, global variables, colour-scheme overrides), `utilities/` (alignment, text-align, visually-hidden), `primitives/` (feedback) and `fonts/`.

A **shape** is a style with no renderer: the caller writes the markup and puts the class on it, and `@use`s the shape from its own stylesheet. `.stack`, `.grid`, `.block`, `.cluster` are shapes. The line against a component is drawn by the files, not by taste — a shape is exactly one `.scss` and nothing renders it, so the day someone writes it a `.html.twig` it stops being a shape and moves into `components/`. That move *is* the promotion, and it is what puts the element in the inventory table: shapes are not in it, because a table of renderers has no column to offer something that has none. The palette file `assets/css/partials/_palette.scss` declares colour variables with `!default` so host apps can override them by importing their own palette first.

### Call path through the stack

A typical page request arrives at a controller that calls `renderPage('index')`. The loader's `AbstractPagesController` builds a `RenderPass` (tracking the bundle, view name, and layout bases), passes it through `adaptiveRender()`, and ultimately calls `twig->render()`. The template extends `dashboard/layout.html.twig` → `default/layout.html.twig` → the loader's HTML base, which owns the `<!DOCTYPE html>` shell.

Inside a template, calling `{{ button_target(..., 'modal') }}` invokes `ButtonExtension`, which calls the loader's `ComponentsExtension::component()`. That function renders `components/button-target/button-target.html.twig` server-side and registers the component with the render pass so the loader emits the correct JS bootstrap data. When the browser executes that bootstrap data, `button-target.ts` mounts, listens for clicks, and delegates to `ModalService`, which fetches the target page and hands it to `modal.ts` — an `AbstractOverlayPageManager` — to display.

UI state flows in the reverse direction: `menu-collapsible-panel.ts` fires `app.onMenuStateChange(id, open)` → `App::persistUiState` POSTs to `/_ui-state/set` → `UiStateController` writes to the session → on the next page load `ui_state_get('ui.layout.menu.left')` returns the saved value and `dashboard/layout.html.twig` renders the panel pre-collapsed or pre-open.

## Integration in the Suite

This package is part of the Wexample Suite — a collection of high-quality, modular tools designed to work seamlessly together across multiple languages and environments.

### Related Packages

The suite includes packages for configuration management, file handling, prompts, and more. Each package can be used independently or as part of the integrated suite.

Visit the [Wexample Suite documentation](https://docs.wexample.com) for the complete package ecosystem.

## Dependencies

- php: >=8.5
- wexample/symfony-live: >=4.0.0
- wexample/symfony-loader: >=10.0.0

## Versioning & Compatibility Policy

Wexample packages follow **Semantic Versioning** (SemVer):

- **MAJOR**: Breaking changes
- **MINOR**: New features, backward compatible
- **PATCH**: Bug fixes, backward compatible

We maintain backward compatibility within major versions and provide clear migration guides for breaking changes.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

Free to use in both personal and commercial projects.

## About us

[Wexample](https://wexample.com) stands as a cornerstone of the digital ecosystem — a collective of seasoned engineers, researchers, and creators driven by a relentless pursuit of technological excellence. More than a media platform, it has grown into a vibrant community where innovation meets craftsmanship, and where every line of code reflects a commitment to clarity, durability, and shared intelligence.

This packages suite embodies this spirit. Trusted by professionals and enthusiasts alike, it delivers a consistent, high-quality foundation for modern development — open, elegant, and battle-tested. Its reputation is built on years of collaboration, refinement, and rigorous attention to detail, making it a natural choice for those who demand both robustness and beauty in their tools.

Wexample cultivates a culture of mastery. Each package, each contribution carries the mark of a community that values precision, ethics, and innovation — a community proud to shape the future of digital craftsmanship.

## Migration Notes

When upgrading between major versions, refer to the migration guides in the documentation.

Breaking changes are clearly documented with upgrade paths and examples.
