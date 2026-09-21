<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\Routing\RouterInterface;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Attribute\PageSection;
use Wexample\SymfonyDesignSystem\Service\RouteGroupRegistry;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

/**
 * The zones a page offers, filled by whoever asked to be in them.
 *
 * A page names a hole and nothing else; what goes in it is declared on the
 * other side, by the routes carrying `#[PageSection]`. So an application can
 * open its pages to bundles it has never heard of, and a bundle can add to a
 * page it does not own.
 *
 * Each section is drawn by a sub-request through the kernel — the section is a
 * route, and a route is entered the way routes are entered, with its own
 * controller, its own services and its own security. Inline, so no fragment
 * route has to be exposed for this to work.
 */
class SectionExtension extends AbstractExtension
{
    public function __construct(
        private readonly RouteGroupRegistry $routeGroupRegistry,
        private readonly FragmentHandler $fragmentHandler,
        private readonly RouterInterface $router,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'page_sections',
                [$this, 'pageSections'],
                [
                    self::FUNCTION_OPTION_IS_SAFE => self::FUNCTION_OPTION_IS_SAFE_VALUE_HTML,
                ]
            ),
        ];
    }

    /**
     * Every section of a zone, in weight order, drawn one after the other.
     *
     * The empty string of a zone nobody joined is what lets a page put a
     * heading above its sections only when there are sections.
     */
    public function pageSections(
        string $zone,
        array $routeParams = []
    ): string {
        $rendered = '';

        foreach ($this->routeGroupRegistry->getGroup(PageSection::class, $zone) as $route) {
            $rendered .= (string) $this->fragmentHandler->render(
                $this->router->generate($route, $routeParams)
            );
        }

        return $rendered;
    }
}
