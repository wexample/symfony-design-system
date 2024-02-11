<?php

namespace Wexample\SymfonyDesignSystem\Service\RenderNodeUsage;

use Exception;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Service\AssetsRegistryService;
use Wexample\SymfonyHelpers\Helper\PathHelper;

abstract class AbstractAssetUsageService
{
    public function __construct(
        protected AssetsRegistryService $assetsRegistryService
    ) {

    }

    public function buildBuiltPublicAssetPath(
        AbstractRenderNode $renderNode,
        string $ext
    ): string {
        $nameParts = explode('::', $renderNode->name);

        return AssetsRegistryService::DIR_BUILD.PathHelper::join([$nameParts[0], $ext, $nameParts[1].'.'.$ext]);
    }

    abstract public function addAssetsForRenderNodeAndType(
        AbstractRenderNode $renderNode,
        string $ext
    ): void;

    protected function createAssetIfExists(
        string $pathRelativeToPublic,
        AbstractRenderNode $renderNode,
    ): ?Asset {
        if (!$this->assetsRegistryService->assetExists($pathRelativeToPublic)) {
            return null;
        }

        $realPath = $this->assetsRegistryService->getRealPath($pathRelativeToPublic);

        if (!$realPath) {
            throw new Exception('Unable to find asset "'.$pathRelativeToPublic.'" in manifest.');
        }

        $asset = new Asset(
            $pathRelativeToPublic
        );

        $renderNode->assets[$asset->type][] = $asset;

        $this->assetsRegistryService->addAsset(
            $asset,
        );

        return $asset;
    }
}