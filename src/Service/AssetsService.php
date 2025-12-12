<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\WebRenderNode\Rendering\RenderNode\AbstractRenderNode;

class AssetsService
{
    public function assetsDetect(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        ?string $view = null
    ): void {
    }
}
