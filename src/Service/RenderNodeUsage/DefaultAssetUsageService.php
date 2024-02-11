<?php

namespace Wexample\SymfonyDesignSystem\Service\RenderNodeUsage;

use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;

class DefaultAssetUsageService extends AbstractAssetUsageService
{
    const NAME = 'default';

    public function addAssetsForRenderNodeAndType(
        AbstractRenderNode $renderNode,
        string $ext
    ): void {
        $this->createAssetIfExists(
            $this->buildBuiltPublicAssetPath($renderNode, $ext),
            $renderNode,
        );
    }
}