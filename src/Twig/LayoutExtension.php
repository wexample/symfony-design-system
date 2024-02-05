<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Exception;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\LayoutService;
use Wexample\SymfonyDesignSystem\Service\PageService;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

class LayoutExtension extends AbstractExtension
{
    public function __construct(
        private readonly LayoutService $layoutService,
        private readonly PageService $pageService,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'layout_init',
                [
                    $this,
                    'layoutInit',
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
    public function layoutInit(
        Environment $twig,
        RenderPass $renderPass,
        string $layoutName,
        string $pageName,
    ): void {
        $this->layoutService->layoutInitInitial(
            $renderPass,
            $twig,
            $layoutName,
        );

        $this->pageService->pageInit(
            $renderPass->layoutRenderNode->page,
            $pageName,
        );
    }
}
