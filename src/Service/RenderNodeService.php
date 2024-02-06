<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;

abstract class RenderNodeService
{
    public function __construct(
        protected AssetsService $assetsService,
        protected AdaptiveResponseService $adaptiveResponseService,
    ) {
    }

    public function initRenderNode(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        string $name,
    ): void {
        $renderNode->init(
            $renderPass,
            $name
        );

        if ($renderNode->hasAssets) {
            $this->assetsService->assetsDetect(
                $renderNode->name,
                $renderNode,
                $renderNode->assets
            );
        }
    }
}
