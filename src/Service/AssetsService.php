<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\WebRenderNode\Asset\AssetManager;
use Wexample\WebRenderNode\Rendering\RenderNode\AbstractRenderNode;

class AssetsService extends AssetManager
{
    public function getUsages(): array
    {
        return [];
    }

    public function assetsDetect(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        ?string $view = null
    ): void {
    }
}
