<?php

namespace Wexample\SymfonyDesignSystem\Service\RenderNodeUsage;

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
        AbstractRenderNode $renderNode,
        string $ext
    ): void {
        $this->createAssetIfExists(
            $this->buildBuiltPublicAssetPath($renderNode, $ext),
            $renderNode,
        );
    }

    public function isAssetReadyForServerSideRendering(
        Asset $asset,
        RenderPass $renderPass,
    ): bool {
        if ($asset->type === Asset::EXTENSION_JS) {
            return $renderPass->useJs;
        }

        return true;
    }
}