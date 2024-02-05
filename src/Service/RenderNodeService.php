<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;

abstract class RenderNodeService
{
    public function __construct(
        protected AdaptiveResponseService $adaptiveResponseService,
    ) {
    }

    public function initRenderNode(
        AbstractRenderNode $renderNode,
        string $name,
    ): void {
        $renderNode->init(
            $name
        );
    }
}
