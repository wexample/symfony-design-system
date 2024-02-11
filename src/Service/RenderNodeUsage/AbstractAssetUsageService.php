<?php

namespace Wexample\SymfonyDesignSystem\Service\RenderNodeUsage;

use Exception;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Service\AssetsRegistryService;

abstract class AbstractAssetUsageService
{
    public function __construct(
        protected AssetsRegistryService $assetsRegistryService
    ) {

    }

    abstract public function addAssetsForRenderNodeAndType(
        AbstractRenderNode $renderNode,
        string $ext
    ): void;

    protected function createAsset(string $pathRelativeToPublic): ?Asset
    {
        if (!$this->assetsRegistryService->assetExists($pathRelativeToPublic)) {
            return null;
        }

        $realPath = $this->assetsRegistryService->getRealPath($pathRelativeToPublic);
        
        if (!$realPath) {
            throw new Exception('Unable to find asset "'.$pathRelativeToPublic.'" in manifest.');
        }


        return new Asset(
            $pathRelativeToPublic
        );
    }
}