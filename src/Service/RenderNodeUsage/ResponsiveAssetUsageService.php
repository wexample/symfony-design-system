<?php

namespace Wexample\SymfonyDesignSystem\Service\RenderNodeUsage;

use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;

class ResponsiveAssetUsageService extends AbstractAssetUsageService
{
    const NAME = 'responsive';

    public function buildAssetsPathsForRenderNodeAndType(
        AbstractRenderNode $renderNode,
        string $ext
    ): array {
        return [];
    }
}