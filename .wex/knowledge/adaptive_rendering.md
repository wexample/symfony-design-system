# Adaptive rendering (current state)

The design-system controllers now pivot around a light `RenderPass` object instead of calling `render()` directly.
Each controller can override `configureRenderPass()` to inject layout-specific tweaks, while `adaptiveRender()` builds a
render-pass from the view name and hands it to `renderRenderPass()`.

`AbstractDesignSystemController` centralizes the wiring:
- `createRenderPass()` instantiates `RenderPass` with the view and lets controllers customize it if needed.
- `adaptiveRender()`/`renderRenderPass()` funnel every response through that render-pass before delegating to Twig.
- Helpers such as `getTemplateLocationPrefix()` and `getControllerTemplateDir()` resolve the correct Twig namespace based on
  the controller bundle, keeping bundle-specific templates discoverable automatically.

At this stage adaptive rendering basically guarantees that all controllers follow the same rendering pipeline and template
resolution logic, preparing the ground for future hooks (asset aggregation, layout metadata, etc.).
