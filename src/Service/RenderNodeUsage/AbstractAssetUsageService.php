<?php

namespace Wexample\SymfonyDesignSystem\Service\RenderNodeUsage;

use Exception;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AssetsRegistryService;
use Wexample\SymfonyHelpers\Helper\PathHelper;

abstract class AbstractAssetUsageService
{
    public function __construct(
        protected AssetsRegistryService $assetsRegistryService
    ) {

    }

    abstract public static function getName(): string;

    public function buildBuiltPublicAssetPath(
        AbstractRenderNode $renderNode,
        string $ext
    ): string {
        $nameParts = explode('::', $renderNode->name);

        return AssetsRegistryService::DIR_BUILD.PathHelper::join([$nameParts[0], $ext, $nameParts[1].'.'.$ext]);
    }

    abstract public function addAssetsForRenderNodeAndType(
        RenderPass $renderPass,
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
            $pathRelativeToPublic,
            $this::getName()
        );

        $renderNode->assets[$asset->type][] = $asset;

        $this->assetsRegistryService->addAsset(
            $asset,
        );

        return $asset;
    }
}