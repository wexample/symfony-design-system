<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Exception;
use JetBrains\PhpStorm\Pure;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\PageRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyTranslations\Translation\Translator;

class LayoutService extends RenderNodeService
{
    #[Pure]
    public function __construct(
        AssetsService $assetsService,
        private readonly PageService $pageService,
        protected Translator $translator,
    ) {
        parent::__construct(
            $assetsService,
        );
    }

    /**
     * @throws Exception
     */
    public function layoutInitInitial(
        RenderPass $renderPass,
        string $layoutPath,
    ): void {
        $this->layoutInit(
            $renderPass,
            $renderPass->layoutRenderNode,
            $layoutPath,
        );

        $renderPass->layoutRenderNode->page = new PageRenderNode();
        $renderPass->layoutRenderNode->page->isInitialPage = true;

        $this->pageService->pageInit(
            $renderPass,
            $renderPass->layoutRenderNode->page,
            $renderPass->getView(),
        );
    }

    /**
     * @throws Exception
     */
    public function layoutInit(
        RenderPass $renderPass,
        AbstractLayoutRenderNode $layoutRenderNode,
        string $layoutPath,
    ) {
        $this->initRenderNode(
            $layoutRenderNode,
            $renderPass,
            $layoutPath,
        );

        $renderPass->setCurrentContextRenderNode(
            $layoutRenderNode
        );
    }
}
