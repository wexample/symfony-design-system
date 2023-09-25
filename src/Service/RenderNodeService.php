<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Wexample\SymfonyDesignSystem\Rendering\RenderNode\RenderNode;

abstract class RenderNodeService
{
    public function __construct(
        protected AssetsService $assetsService,
        protected AdaptiveResponseService $adaptiveResponseService,
    ) {
    }

    public function initRenderNode(
        RenderNode $renderNode,
        string $name,
        string $useJs
    ) {
        $renderNode->init(
            $name
        );

        if ($renderNode->hasAssets) {
            $this->assetsService->assetsDetect(
                $renderNode->name,
                $renderNode,
                $renderNode->assets
            );

            $this->assetsService->assetsPreload(
                $renderNode->assets['css'],
                $this->adaptiveResponseService->renderPass->layoutRenderNode->colorScheme,
                $useJs,
            );
        }
    }
}
