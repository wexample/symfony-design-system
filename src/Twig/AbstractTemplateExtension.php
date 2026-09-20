<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;
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
     * @param mixed $context the twig context, holding the render pass
     */
    public function renderComponent(
        Environment $twig,
        mixed $context,
        string $name,
        array $options = [],
        array $templateVars = []
    ): string {
        return $this->componentsExtension->component(
            $twig,
            is_array($context) ? ($context['render_pass'] ?? null) : null,
            $name,
            array_merge($this->getDefaultOptions(), $options),
            $templateVars
        );
    }
}
