<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Exception;
use JetBrains\PhpStorm\Pure;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyTranslations\Translation\Translator;

class LayoutService extends AbstractRenderNodeService
{
    #[Pure]
    public function __construct(
        AssetsService $assetsService,
        private readonly PageService $pageService,
        protected readonly Translator $translator,
    )
    {
        parent::__construct(
            $assetsService,
        );
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
        $layoutRenderNode = $renderPass->getLayoutRenderNode();

        $this->initRenderNode(
            $layoutRenderNode,
            $renderPass,
            // The default view should have been defined into the layout template.
            $layoutRenderNode->getView(),
        );

        $this->translator->setDomainFromTemplatePath(
            $layoutRenderNode->getContextType(),
            $layoutRenderNode->getView(),
        );

        $this->pageService->pageInit(
            $renderPass,
            $layoutRenderNode->createLayoutPageInstance(),
            $renderPass->getView(),
        );
    }
}
