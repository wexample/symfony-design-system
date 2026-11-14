<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use LogicException;
use Twig\Environment;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;
use Wexample\SymfonyLoader\Rendering\RenderPass;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

/**
 * The base of every twig function that draws an element of the design system.
 *
 * A function is not a way of drawing beside the component — it *is* how a
 * component is called, with named options and defaults, from a template. So it
 * never renders a template of its own: it hands the component name to the
 * loader, which resolves the folder, loads the stylesheet and the script the
 * element needs, and binds it. That is the whole difference with the include it
 * replaces, and the reason the include had no future: an `include` carries no
 * options contract and loads nothing.
 */
abstract class AbstractTemplateExtension extends AbstractExtension
{
    /**
     * Default TwigFunction options for HTML-rendering helpers.
     *
     * The context comes in because the render pass travels in it, and no
     * component can be registered without one.
     */
    protected const TEMPLATE_FUNCTION_OPTIONS = [
        self::FUNCTION_OPTION_IS_SAFE => self::FUNCTION_OPTION_IS_SAFE_VALUE_HTML,
        self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true,
        self::FUNCTION_OPTION_NEEDS_CONTEXT => true,
    ];

    public function __construct(
        protected readonly ComponentsExtension $componentsExtension,
    ) {
    }

    protected function getDefaultOptions(): array
    {
        return [];
    }

    /**
     * What a function hands the component is template variables, not options.
     *
     * The difference is where they end up: a component's options are serialised
     * into the page for the browser to build the component from, template
     * variables only reach the twig that draws it. What these functions pass is
     * the second kind — a row of entities, a menu's render pass, a translated
     * label already resolved — and sending it to the browser would at best
     * bloat the page and at worst, as it did, produce a `layoutRenderData` that
     * json cannot encode and a page with no data at all.
     *
     * A component whose client side needs options says so by calling
     * `component()` itself, which is what the interactive ones do.
     *
     * @param mixed $context the twig context, holding the render pass
     */
    public function renderComponent(
        Environment $twig,
        mixed $context,
        string $name,
        array $templateVars = [],
        array $options = []
    ): string {
        return $this->componentsExtension->component(
            $twig,
            $this->getContextRenderPass($context, $name),
            $name,
            $options,
            array_merge($this->getDefaultOptions(), $templateVars)
        );
    }

    /**
     * The render pass the context carries. A function called from a template
     * rendered outside the loader (a bare `twig.render`, a mail) has none, and
     * passing that null on ended in a TypeError deep in `component()` that named
     * neither the function nor the component: say which one, here.
     */
    protected function getContextRenderPass(mixed $context, string $name): RenderPass
    {
        $renderPass = is_array($context) ? ($context['render_pass'] ?? null) : null;

        if (! $renderPass instanceof RenderPass) {
            throw new LogicException(
                'The component "'.$name.'" needs a render pass, and the template drawing it has no "render_pass" in its context: render it through the loader.'
            );
        }

        return $renderPass;
    }
}
