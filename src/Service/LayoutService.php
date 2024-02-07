<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Exception;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyTranslations\Translation\Translator;

class LayoutService extends RenderNodeService
{
    #[Pure]
    public function __construct(
        AssetsService $assetsService,
        AdaptiveResponseService $adaptiveResponseService,
        KernelInterface $kernel,
        private readonly PageService $pageService,
        protected Translator $translator,
    ) {
        parent::__construct(
            $assetsService,
            $adaptiveResponseService,
            $kernel
        );
    }

    /**
     * @throws Exception
     */
    public function layoutInitInitial(
        RenderPass $renderPass,
        Environment $twig,
        string $layoutPath,
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
            $renderPass->view,
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
            $layoutPath,
        );
    }
}
