<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Exception;
use JetBrains\PhpStorm\Pure;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyTranslations\Translation\Translator;

class LayoutService extends RenderNodeService
{
    #[Pure]
    public function __construct(
        AssetsService $assetsService,
        readonly protected ComponentService $componentService,
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
        Environment $twig,
        RenderPass $renderPass,
    ): void {
        $this->layoutInit($renderPass);

        if ($renderPass->getLayoutBase() === RenderPass::BASE_MODAL) {
            // Prepare modal component.
            $this->componentService->componentInitLayout(
                $twig,
                $renderPass,
                ComponentService::buildCoreComponentName(ComponentService::COMPONENT_NAME_MODAL),
                [
                    'adaptiveResponsePageManager' => true,
                ]
            );
        }
    }

    /**
     * @throws Exception
     */
    public function layoutInit(
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
