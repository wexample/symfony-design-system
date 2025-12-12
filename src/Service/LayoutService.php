<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Exception;
use JetBrains\PhpStorm\Pure;
use Twig\Environment;
use Wexample\WebRenderNode\Rendering\RenderPass;


class LayoutService extends RenderNodeService
{
    #[Pure]
    public function __construct(
        private readonly PageService $pageService,
    )
    {

    }

    /**
     * @throws Exception
     */
    public function layoutInitialInit(
        Environment $twig,
        RenderPass $renderPass,
    ): void
    {
        $this->layoutInit($renderPass);
    }

    /**
     * @throws Exception
     */
    public function layoutInit(
        RenderPass $renderPass,
    ): void
    {
        $layoutRenderNode = $renderPass->layoutRenderNode;

        $this->pageService->pageInit(
            $renderPass,
            $layoutRenderNode->createLayoutPageInstance(),
            $renderPass->getView(),
        );
    }
}
