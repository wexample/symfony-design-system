<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Wexample\SymfonyDesignSystem\Rendering\RenderNode\Traits\DesignSystemRenderNodeTrait;
use Wexample\WebRenderNode\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;

abstract class AbstractRenderNodeService
{
    public function __construct(
        protected AssetsService $assetsService,
    ) {
    }

    /**
     * @param AbstractRenderNode|DesignSystemRenderNodeTrait $renderNode
     * @param RenderPass $renderPass
     * @param string $view
     * @return void
     *
     * Render node path or name are created after class construction,
     * as layout name is given by the template and so undefined
     * on layout render node class instantiation.
     */
    public function initRenderNode(
        AbstractRenderNode $renderNode,
        RenderPass $renderPass,
        string $view,
    ): void {
        $renderNode->init(
            $renderPass,
            $view,
        );

        if ($renderNode->hasAssets()) {
            $this->assetsService->assetsDetect(
                $renderPass,
                $renderNode,
            );
        }
    }
}
