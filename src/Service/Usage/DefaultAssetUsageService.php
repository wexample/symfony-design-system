<?php

namespace Wexample\SymfonyDesignSystem\Service\Usage;

use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;

final class DefaultAssetUsageService extends AbstractAssetUsageService
{
    public static function getName(): string
    {
        return 'default';
    }

    public function addAssetsForRenderNodeAndType(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        string $ext
    ): void {
        $this->createAssetIfExists(
            $this->buildBuiltPublicAssetPath($renderNode, $ext),
            $renderNode,
        );
    }

    public function assetNeedsInitialRender(
        Asset $asset,
        RenderPass $renderPass,
    ): bool {
        if ($asset->type === Asset::EXTENSION_JS) {
            return $renderPass->isUseJs();
        }

        return true;
    }
}