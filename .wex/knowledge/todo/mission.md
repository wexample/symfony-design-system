# Mission recap: symfony-design-system redesign

## Current scope
- Remaining bundle = minimal Symfony design system skeleton (controllers + base rendering pipeline), most assets/services removed.
- Core goal: phase legacy features back in cleanly, while extracting neutral pieces into shared packages (`php-web-render-node`, future JS counterpart, etc.).
- Reference legacy located at `packages/PHP/draft/tmp/symfony-design-system` for behaviour comparison.

## Adaptive rendering status
- Controllers use a `RenderPass` (now DS-specific) that builds layout/page render nodes, later injected into templates via Twig macros.
- HTML layout base extends `@.../bases/html/default.html.twig` and injects asset placeholders + initial layout data via `layout_render_initial_data`.
- Assets/debug blocks were moved from an event subscriber to `AbstractDesignSystemController::injectLayoutAssets()`.

## Render nodes contract
- Shared package `wexample/php-web-render-node`: minimal DTOs/traits for render nodes (`RenderPass`, `AbstractRenderNode`, `InitialLayoutRenderNode`, `PageRenderNode`, etc.).
- DS-specific bundle extends these nodes (e.g. `Rendering\RenderNode\InitialLayoutRenderNode` with `DesignSystemRenderNodeTrait`) to add legacy logic (view defaults, ID generation).
- JSON schema for render node payload lives in `packages/COMMON/repo/web-render-node/schema.json`.

## Next steps (high-level)
1. Ensure layout/page render nodes serialize full contract (view, id, vars, assets, translations, usages) and populate `<script id="layout-data">` + placeholders.
2. Reintroduce asset management: `AssetsService` + render node assets should load CSS/JS entries like the legacy (ideally using contract payload).
3. Prepare AJAX/JSON rendering path: legacy had `AjaxLayoutRenderNode` & JSON responses, needs to be reimplemented to match the new contract.
4. Gradually reimport legacy controllers/layouts/assets, keeping non-DS logic (render node DTOs, schema) inside shared packages.
5. Mirror schema on the front end (future JS package) to consume `window.appRegistry.layoutRenderData` and progressively migrate to the contract.

## Notes
- Keep `.wex/knowledge` files updated as features migrate (adaptive_rendering.md, web-render-contract, etc.).
- All destructive features currently removed should be re-added carefully referencing the legacy repo.
- When implementing new features, prefer extracting generic pieces to `php-web-render-node` and tracking schema changes in `web-render-node/schema.json`.
