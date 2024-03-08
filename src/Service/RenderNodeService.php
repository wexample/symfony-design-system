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

    /**
     * Render node path or name are created after class construction,
     * as layout name is given by the template and so undefined
     * on layout render node class instanciation.
     */
    public function initRenderNode(
        AbstractRenderNode $renderNode,
        RenderPass $renderPass,
        string $path,
    ): void {
        $renderNode->init(
            $renderPass,
            $path,
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
