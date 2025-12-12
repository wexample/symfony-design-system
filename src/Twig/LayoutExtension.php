<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Exception;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\LayoutService;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

class LayoutExtension extends AbstractExtension
{
    public function __construct(
        private readonly LayoutService $layoutService,
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'layout_initial_init',
                [
                    $this,
                    'layoutInitialInit',
                ],
                [
                    self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true,
                ]
            ),
        ];
    }

    /**
     * @throws Exception
     */
    public function layoutInitialInit(
        Environment $twig,
        RenderPass $renderPass,
    ): void
    {
        $this->layoutService->layoutInitialInit(
            $twig,
            $renderPass,
        );
    }
}
