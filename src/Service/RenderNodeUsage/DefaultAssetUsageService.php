<?php

namespace Wexample\SymfonyDesignSystem\Service\RenderNodeUsage;

use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Service\AssetsRegistryService;
use Wexample\SymfonyHelpers\Helper\PathHelper;

class DefaultAssetUsageService extends AbstractAssetUsageService
{
    const NAME = 'default';

    public function addAssetsForRenderNodeAndType(
        AbstractRenderNode $renderNode,
        string $ext
    ): void {
        $asset = $this->createAsset(
            $this->buildBuiltPublicAssetPath($renderNode, $ext)
        );

        if ($asset) {
            $renderNode->assets[$ext][] = $asset;

            $this->assetsRegistryService->addAsset(
                $asset,
            );
        }
    }

    public function buildBuiltPublicAssetPath(
        AbstractRenderNode $renderNode,
        string $ext
    ): string {
        $nameParts = explode('::', $renderNode->name);

        return AssetsRegistryService::DIR_BUILD.PathHelper::join([$nameParts[0], $ext, $nameParts[1].'.'.$ext]);
    }
}