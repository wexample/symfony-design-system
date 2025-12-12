<?php

namespace Wexample\SymfonyDesignSystem\Service\Usage\Traits;

use Wexample\SymfonyDesignSystem\Rendering\RenderNode\Traits\DesignSystemRenderNodeTrait;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\WebRenderNode\Rendering\RenderNode\AbstractRenderNode;

trait DesignSystemUsageServiceTrait
{
    /**
     * @param RenderPass $renderPass
     * @param AbstractRenderNode|DesignSystemRenderNodeTrait $renderNode
     * @param string $ext
     * @param string $view
     * @return bool
     */
    public function addAssetsForRenderNodeAndType(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        string $ext,
        string $view
    ): bool
    {
        # TODO
        return true;
    }
}
