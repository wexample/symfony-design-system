# Web render contract

Goal: define a single schema for render nodes, assets, and metadata that both PHP (backend) and TS (frontend) must obey.

Planned steps:
1. **Spec first** – formalize the JSON structure produced by `RenderNode::toRenderData()` (node fields, `assets.css/js`, translations, etc.). Document it as the reference “web-render-contract”.
2. **Split packages** – create two tiny packages (`php-web-render-contract`, `js-web-render-contract`) that expose the same schema/DTOs/types. They only host the contract: no rendering logic.
3. **Adopt incrementally** – make the Symfony design-system bundle serialize render nodes via the PHP contract, while the legacy front-end consumes them through the JS contract. Older code can adapt gradually by targeting the same schema.

Outcome: render nodes and their assets become predictable across the stack, avoiding ad-hoc JSON and easing future refactors or consumers (CLI tools, other renderers, etc.).
