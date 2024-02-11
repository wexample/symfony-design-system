<?php

namespace Wexample\SymfonyDesignSystem\Service\RenderNodeUsage;

use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;

abstract class AbstractAssetUsageService
{
    abstract public function buildAssetsPathsForRenderNodeAndType(
        AbstractRenderNode $renderNode,
        string $ext
    ): array;
}