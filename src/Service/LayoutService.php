<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Exception;
use JetBrains\PhpStorm\Pure;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\PageRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyTranslations\Translation\Translator;

class LayoutService extends RenderNodeService
{
    #[Pure]
    public function __construct(
        AssetsService $assetsService,
        readonly private PageService $pageService,
        readonly protected Translator $translator,
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
    ): void {
        $layoutRenderNode = $renderPass->layoutRenderNode;

        $this->initRenderNode(
            $layoutRenderNode,
            $renderPass,
            $layoutRenderNode->getView(),
        );

        $this->pageService->pageInit(
            $renderPass,
            $layoutRenderNode->createLayoutPageInstance(),
            $renderPass->getView(),
        );
    }
}
