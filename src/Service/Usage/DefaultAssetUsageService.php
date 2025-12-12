<?php

namespace Wexample\SymfonyDesignSystem\Service\Usage;

use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\Usage\Traits\DesignSystemUsageServiceTrait;
use Wexample\WebRenderNode\Rendering\RenderNode\AbstractRenderNode;
use Wexample\WebRenderNode\Usage\DefaultUsage;

final class DefaultAssetUsageService extends DefaultUsage
{
    use DesignSystemUsageServiceTrait;

    public function addAssetsForRenderNodeAndType(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        string $ext,
        string $view
    ): bool
    {
        return (bool) $this->createAssetIfExists(
            renderPass: $renderPass,
            pathInManifest: $this->buildPublicAssetPathFromView(
                $view,
                $ext
            ),
            view: $view,
            renderNode: $renderNode,
        );
    }
}
