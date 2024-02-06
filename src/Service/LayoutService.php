<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Exception;
use JetBrains\PhpStorm\Pure;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyTranslations\Translation\Translator;

class LayoutService extends RenderNodeService
{
    #[Pure]
    public function __construct(
        AssetsService $assetsService,
        AdaptiveResponseService $adaptiveResponseService,
        private readonly PageService $pageService,
        protected Translator $translator
    ) {
        parent::__construct(
            $assetsService,
            $adaptiveResponseService
        );
    }

    /**
     * @throws Exception
     */
    public function layoutInitInitial(
        RenderPass $renderPass,
        Environment $twig,
        string $layoutPath,
        string $pageName,
    ): void {
        $this->layoutInit(
            $renderPass,
            $twig,
            $renderPass->layoutRenderNode,
            $layoutPath,
        );

        $this->pageService->pageInit(
            $renderPass,
            $renderPass->layoutRenderNode->page,
            $pageName,
        );
    }

    /**
     * @throws Exception
     */
    public function layoutInit(
        RenderPass $renderPass,
        Environment $twig,
        AbstractLayoutRenderNode $layoutRenderNode,
        string $layoutPath,
    ) {
        $this->initRenderNode(
            $renderPass,
            $layoutRenderNode,
            RenderingHelper::renderNodeNameFromPath($layoutPath),
        );
    }
}
