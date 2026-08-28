# Symfony Design System – Mission & Status

## Mission recap
- Remaining bundle = minimal Symfony design-system skeleton (controllers + rendering pipeline) with most legacy assets/services removed.
- Core goal: reimport legacy features cleanly while pushing neutral pieces into shared packages (`php-web-render-node`, future JS counterpart, etc.).
- Use `packages/PHP/draft/tmp/symfony-design-system` as behaviour reference; destructive changes should lean on that repo.

### Adaptive rendering snapshot
- Controllers rely on a DS-specific `RenderPass` to build layout/page render nodes that Twig injects via macros.
- The HTML base extends `@.../bases/html/default.html.twig`, wiring asset placeholders + `layout_render_initial_data`.
- Asset/debug injection now happens in `AbstractDesignSystemController::injectLayoutAssets()`.

### Render nodes contract
- Shared package `wexample/php-web-render-node` exposes DTOs and schema helpers (`RenderPass`, `AbstractRenderNode`, `InitialLayoutRenderNode`, `PageRenderNode`, etc.).
- DS nodes extend them via `DesignSystemRenderNodeTrait` / `DesignSystemLayoutRenderNodeTrait` to add IDs, default views, translation domains, etc.
- Contract/schema source of truth: `packages/COMMON/repo/web-render-node/schema.json`.

### Next milestones (legacy recap)
1. Render nodes must serialize the full contract (view, id, vars, assets, translations, usages) and hydrate `<script id="layout-data">` and asset placeholders.
2. Asset management (services + render-node assets) has to match legacy behaviour using the shared contract payload.
3. AJAX/JSON rendering path (AjaxLayoutRenderNode + JSON responses) needs parity with the new contract.
4. Legacy controllers/layouts/assets should be reintroduced progressively, isolating generic logic into shared packages.
5. Mirror the schema on the frontend (future `js-web-render-node`) so `window.appRegistry.layoutRenderData` consumers converge on one protocol.

## Current status

### Rendering pipeline
- `AbstractDesignSystemController::adaptiveRender()` instantiates the DS `RenderPass`, initializes `InitialLayoutRenderNode`, injects assets/debug blocks, then emits the response.
- Twig bases: `assets/bases/base.html.twig` triggers `layout_initial_init()` (bootstraps layout/page services); `assets/bases/html/default.html.twig` injects `<title>`, asset placeholders, and initial layout data through `assets/macros/assets.html.twig`.
- `LayoutExtension` offers Twig functions `layout_initial_init` (service initialization) and `layout_render_initial_data` (serializes layout node into `window.appRegistry.layoutRenderData`).

### Render nodes & contract
- DTOs for view/id/components/vars/usages/assets live inside `wexample/php-web-render-node`, extended in this bundle with DS-specific traits for defaults and ID generation.
- Contract tracked via `.wex/knowledge` and `packages/COMMON/repo/web-render-node/schema.json`.

### Assets & usages
- Usage types restored (default, responsive, color_scheme, margins, animations, fonts); configs read from `design_system.usages.*`. `RenderPass` now stores both `usagesConfig` and current `usages`.
- `AssetsService` mixes the contract-first payload with legacy lookups, looping usages/extensions and rebuilding public paths from view names through `DesignSystemUsageServiceTrait`.
- `AssetsRegistry` has returned: reads `public/build/manifest.json`, resolves built vs real paths, and ensures only manifest-backed assets are created.
- Asset DTOs currently carry basic metadata (path/view/type/usage/context/flags); remaining work: DOM IDs, SSR flags, tag generation.

### Shared helpers/packages
- `wexample/php-web-render-node` now includes base render pass/nodes, asset DTOs/registry, usage scaffolding, and schema helpers.
- The design-system bundle layers DS-specific behaviours (ID generation, translation domain, asset detection) while keeping serialization contract-compliant.

### Outstanding tasks
1. Finish `DesignSystemUsageServiceTrait::createAssetIfExists()` (DOM IDs, SSR flags, registry parity).
2. Reintroduce the AJAX render path with contract-compliant JSON payloads.
3. Produce `AssetTag`/contract-compliant bundles to inject `<link>/<script>` tags server-side.
4. Reimport legacy layouts/components/assets from `packages/PHP/draft/tmp/symfony-design-system`, documenting the journey.
5. Ship the JS-side contract (`js-web-render-node`) so front-end code consumes `layoutRenderData` directly.

## Web render contract

Goal: a unified schema for render nodes, assets, and metadata that both PHP (backend) and TS/JS (frontend) implement.

Steps:
1. **Spec first** – formalize JSON output from `RenderNode::toRenderData()` (node fields, `assets.css/js`, translations, etc.) and document it as the canonical contract.
2. **Split packages** – minimal `php-web-render-node` / `js-web-render-node` packages exposing the same schema/DTOs/types and nothing else.
3. **Adopt incrementally** – Symfony bundle serializes via the PHP contract, while the legacy front-end consumes through the JS contract until everything targets the new schema.
4. **Protocol repo** – language-agnostic schema in `packages/COMMON/repo/web-render-node`, with PHP/JS packages just implementing that spec.

Outcome: predictable render-node payloads across the stack, simplifying future refactors and third-party consumers.

## Objectives / backlog

- Migrate the useful legacy code from `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/PACKAGES/PHP/backup/symfony-design-system`.
- Reactivate historical unit tests and harden the core with new ones (render pass, asset registry, AJAX responses, JS/no-JS switches).
- Reimport what belongs in this bundle (extensions, nuanced configuration, panel/modal bases + core JS: bootstrap/app/layout) and cover it with tests.
- Move Demo/Test sections into a dedicated `symfony-design-system-demo` bundle and split theme assets into per-theme bundles (`symfony-ds-theme-admin`, `...-tailwind`, `...-black`, etc.).
- Make “bases” configurable instead of hard-coded (modal/panel/overlay/page) and annotate `assets.html.twig` sections so JS tooling can hook into them.
- Run several cleanup passes (consistency, helper extraction, documentation), expand test coverage, and ensure `php-web-render-node` stays fully portable to the upcoming JS implementation (type parity, schema diffs, fixture-based validation).
- Document how render-request IDs, asset registries, and layout/page context stacks interact so future contributors understand the lifecycle; consider adding diagrams or sequence docs in `.wex/knowledge`.
