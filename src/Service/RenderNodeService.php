<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;

abstract class RenderNodeService
{
    public function __construct(
        protected AssetsService $assetsService,
    ) {
    }

    public function initRenderNode(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        string $path,
    ): void {
        $renderNode->init(
            $renderPass,
            $this->assetsService->buildTemplateNameFromPath($path)
        );

        if ($renderNode->hasAssets) {
            $this->assetsService->assetsDetect(
                $renderPass,
                $renderNode
            );
        }
    }
}
