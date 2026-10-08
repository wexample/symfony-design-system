# symfony_design_system

Version: 32.0.0

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

The same `assets/` directory is also an npm package, `@wexample/symfony-design-system`, which an application must declare as `link:` and not `file:` — the page *Assets as an npm package*, in this same section, gives the mechanism and the failure it prevents.

Three loader services draw markup — a banner, an overlay backdrop, a confirm dialog — and the loader does not know what that markup is. assets/js/Services/BannerService.ts and assets/js/Services/OverlayService.ts extend the loader's and set the one thing it left open, `componentPath`; assets/js/Services/ConfirmService.ts lives here outright, since nothing in the loader calls it. An application registers these three in its `App.getServices()`; the loader lets a subclass take the place of the service registered under the same name, so the base arriving first through `super.getServices()` does not win.

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
| src/Twig/SectionExtension.php | `page_sections($zone, $routeParams)` — the zones a page offers, filled by the routes carrying `#[PageSection]` |
| src/Twig/MessageExtension.php | `message_info()`, `message_success()`, `message_warning()`, `message_error()` — all render `components/message/message.html.twig` with a type and a default icon |
| src/Twig/PropertiesExtension.php | `properties($items, $options)` — key/value list, options `bordered`, `split`, `compact`, `stacked` map to `properties--*` modifiers |
| src/Twig/TabExtension.php | `tab_item()`, `tab_item_link()` — render `components/tab-item/tab-item.html.twig` |
| src/Twig/TableExtension.php | `data_table($columns, $rows, $options)` — normalizes the column definitions, renders `components/data-table/data-table.html.twig` |
| src/Twig/UiStateExtension.php | `ui_state_get($key, $default)` — reads from `session['ui_state.{key}']` |

`button_target($icon, $label, $href, $target, $options)` takes the same first three arguments as `button_link()`, plus where the page it points at is loaded: `modal`, `panel`, or the name of an embed the page holds. It merges `href` and `target` into `$options` and renders `components/button-target`. The class list is the caller's — `options.class` replaces it entirely, defaulting to `button` — because the same behaviour has to sit on a `.button` and on a `.table--icon-link`.

#### Three windows: modal, panel, dock

A page the server renders can be loaded in a modal, a panel or a **dock** — `target` `modal`, `panel` or `dock` on a link, `loadIntoTarget()` in a script. The dock is a window docked at the foot of the screen, beside the page and not over it: no backdrop, no focus held, no place in the overlay stack, so the page stays usable while it is open. The docked windows share one row (`#dock-layer`), each folds to its title from its header (assets/components/dock/dock.ts). The loader knows it as a layout base like the two others (`__layout=dock`, `RenderPass::BASE_DOCK`, its shell in `bases/json/dock.html.twig`, `DockService`).

The three also show what a script puts there itself, without a page from the server: `showInTarget(app, target, { title, body, actions })` in assets/js/Helper/TargetHelper.ts builds the window from its template — the dashboard layout puts the three on every page with `component_frontend()` — and draws the same shell a page gets, in the browser. `body` is markup, or an element moved in as it is, which the script can keep up to date: the uploads window is one.

#### Uploads

`upload_dropzone(directory, options)` draws a place files are dropped on or picked from; any kind of file, any size, unless the page says `accept` (extensions or MIME types, as the HTML attribute reads them) or `max_size` (bytes). Those are signed into the address with the directory (src/Class/UploadRules.php): the picker offers only the kinds accepted, and the controller refuses the rest — a file too large or of an extension not taken on its first piece, one whose name says nothing of an accepted kind by its content once whole, deleted then —, with a translated reason the uploads window shows. The directory is the page's to choose and never the browser's: `upload_url()` signs an address for that one directory (src/Service/UploadTokenService.php, with the kernel secret), and src/Controller/UploadController.php at `/_upload/{token}` refuses any other. The loader's `UploadService` sends each file in pieces (`ChunkedUploadTransport`), sized under the request limits PHP announces, a lost piece sent again; src/Service/ChunkedUploadReceiver.php appends them in order and moves the whole into the directory, a name already there giving « name (2).ext ». `UploadTrayService` follows them in a dock: one row each, its progress, a way to give it up. The zone says `upload:done` — bubbling, with the stored name — for the page around it to read its list again.

#### Where a link opens from an embedded page: `target-embedded`

One page lives alone, in a panel, in a modal or in an embed, and what a link does depends on which. Two cases call for something else than the link's usual target, and both are answered by one setting, `target-embedded`:

- **The page is embedded and the link is a plain one.** Left alone, it would replace the whole window and lose what lies under the panel or the modal. The managers drawing such pages — modal, panel, embed — take the click instead (`onLinkLeaving()` in the loader's `PageManagerComponent`, answered by `targetEmbeddedClick()` in assets/js/Helper/TargetHelper.ts). A page that asked for its links to stay in the manager (`data-page-navigation="contained"`) keeps that behaviour first.
- **The link names an embed the page does not hold.** A page opened in a panel has none of the embeds its full-page self places — the PDF pane beside a document. `loadIntoTarget()` checks `EmbedService.has()` and opens the page where `target-embedded` says instead of failing.

`target-embedded` is `panel` by default: a panel over a panel reads, and is less jarring than a panel then a modal. It is set on the link or on any element around it, `data-target-embedded="modal"` (or `panel`, or an embed's name), as `target_embedded` in `button_target()`'s options, `targetEmbedded` on the vue `<button-target>` or in `loadIntoTarget(app, target, href, { targetEmbedded })`. `none` keeps what the link would do otherwise — a link that really has to change page. External links and `target="_blank"` are never concerned.

### Runs the pages declare themselves into

A menu written by hand is a list someone has to edit when a page appears, and a page shipped by a bundle has nobody to edit it — the application owning the template has never heard of it. src/Attribute/MenuItem.php turns that around: a controller action declares which run of items it joins, and the menu asks for the run.

```php
#[MenuItem(group: 'app_application', weight: 10)]
#[Route('/app/{id}/blog/stats', name: 'acme_blog_board_stats')]
public function index(App $managedApp): Response
```

The attribute carries a group and a weight and nothing else, because everything else is already known elsewhere: `menu_item` reads the label and the icon from the page's own translations — `page_title` and `page_icon` — and decides on its own whether it is the page being read. Repeating any of it on the attribute would be a second place to keep in step with the first.

src/Service/RouteGroupRegistry.php walks `RouterInterface::getRouteCollection()`, reflects on each controller through `RouteHelper::resolveMethodReflection()`, and sorts each group by weight then route name — the order bundles are discovered in is the order they were installed in, which is to say no order at all. The walk is held for the request, once per attribute it is asked for, and nothing is written to disk: the collection it reads from is itself compiled and cached, so a cache here would only add a second thing to invalidate when a bundle arrives.

`menu_items($group, $routeParams, $options)` renders a whole run, one `menu-item` per route, and returns an empty string for a group nobody joined. That is what lets a template put a heading above a run only when there is a run:

```twig
{%- set application_items = menu_items('app_application', app_params) -%}
{%- if application_items -%}
    {{ menu_separator('@layout::menu.application') }}
    {{ application_items | raw }}
{%- endif -%}
```

`| raw` is not optional there: twig escapes what it no longer knows came from a renderer once it has been through a variable.

An entry is drawn only for a page the reader may open (src/Service/RouteAccessService.php): `menu_item()`, `menu_items()` and `menu_item_collapsible_from_controller()` ask the firewall's own question before drawing — `access_control`, decided as the access listener decides, then the controller's `#[IsGranted]` naming no subject; one with a subject needs the page's record and is left to the page. A run whose every entry is refused is the empty string, and its heading goes with it. The bundle requires `symfony/security-bundle`, so the three services the answer is read from are always there; `security.access_map` alone is named in src/Resources/config/services.yaml, SecurityBundle aliasing the other two by interface. `menu_item_link()` takes an address, not a route, and is drawn as given.

The same question is asked of the body of a page by src/Attribute/PageSection.php, its twin: a page names a zone and nothing else, and what fills it is declared on the other side.

```php
#[PageSection(group: 'app_general', weight: 10)]
#[Route('/app/{id}/_sections/blog-posts', name: 'acme_blog_board_section_posts')]
public function posts(App $managedApp): Response
```

A section is a route and not a template on purpose. A zone worth having needs its own data — a repository, a service, a query the page's owner cannot guess — and a template would have to be handed a context nobody can write. So `page_sections` draws each one as an inline sub-request through the kernel, with its own controller, its own services and its own security; no fragment route has to be exposed for that to work. The route serves a fragment, so it renders no layout, and its path opens on an underscore since nobody opens it by hand.

Both attributes extend src/Attribute/AbstractRouteGroup.php and are collected by that same registry, keyed by attribute class: a menu item and a page section are the same question, and two walks written apart are two walks that drift apart.

An application that needs a run to hold less than what declared itself into it implements src/Interface/RouteGroupVoterInterface.php, autoconfigured by the bundle's extension. The board does: bundles share one router, so a page contributed by one app's bundle would otherwise appear on every app. The voter is asked at read time and not while collecting — what a route declared never changes, where it is being asked from changes every request.

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

Sorting is one rule written twice, assets/js/Helper/SortHelper.ts and src/Helper/SortHelper.php: the press cycle, empty values last, locale collation, groups kept in place. The two are tested on the same cases — tests/js/SortHelper.test.ts and tests/Unit/Helper/SortHelperTest.php — and a change to one is a change to the other. Where they part: the Vue header is a button and the order a `v-model:sort`, the Twig header a link and the order the page's query.

### Controllers

src/Controller/UiStateController.php exposes `POST /_ui-state/set`. It reads `{key, value}` from the JSON body and writes `session['ui_state.{key}'] = value`. This is the default persistence target for menu-collapse state; apps that need server-side persistence override `App::persistUiState` on the JS side instead of calling this endpoint.

src/Controller/FixturesController.php serves `placeholder.svg` from `assets/fixtures/` at the design-system base route.

src/Traits/SymfonyDesignSystemBundleClassTrait.php returns `WexampleSymfonyDesignSystemBundle::class` as the bundle class name. A controller mixing it in tells the loader which bundle to resolve template namespaces and translation paths against; the preview controllers that do so live in the `symfony-design-system-demo` package, not here.

### Helper

src/Helper/SortHelper.php holds the order of a server table, and what a controller calls to read it from the query: `fromQuery()` lets through only the keys it is given, which is what keeps a crafted address out of an `ORDER BY`.

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

### Tests

PHP tests run with phpunit, in a container of an application that installs the bundle: `php vendor/bin/phpunit --testsuite unit` from the package directory. The TypeScript ones run on node alone, without any dependency, and need a node that strips types (22.18 and up): `node --test 'tests/js/*.test.ts'`. tests/js/hooks.mjs lets node resolve the assets as the bundler does, and load a vue component's script with the components it pulls in left empty, so a component's props, computed and methods can be run on a bare instance — the template is checked in the demo pages.

## Integration in the Suite

This package is part of the Wexample Suite — a collection of high-quality, modular tools designed to work seamlessly together across multiple languages and environments.

### Related Packages

The suite includes packages for configuration management, file handling, prompts, and more. Each package can be used independently or as part of the integrated suite.

Visit the [Wexample Suite documentation](https://docs.wexample.com) for the complete package ecosystem.

## Dependencies

- php: >=8.5
- wexample/symfony-loader: >=22.0.0
- symfony/security-bundle: ^7.4 || ^8.0
- wexample/symfony-routing: >=2.0.0
- wexample/symfony-template: >=2.0.5

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
