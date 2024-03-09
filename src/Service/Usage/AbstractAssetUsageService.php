<?php

namespace Wexample\SymfonyDesignSystem\Service\Usage;

use Exception;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AssetsRegistryService;
use Wexample\SymfonyHelpers\Helper\PathHelper;
use Wexample\SymfonyHelpers\Helper\TextHelper;

abstract class AbstractAssetUsageService
{
    public function __construct(
        protected AssetsRegistryService $assetsRegistryService
    ) {

    }

    abstract public static function getName(): string;

    public function buildPublicAssetPathFromTemplateAbstractPath(
        string $templateAbstractPath,
        string $ext
    ): string {
        $nameParts = explode('::', $templateAbstractPath);

        return AssetsRegistryService::DIR_BUILD.PathHelper::join([$nameParts[0], $ext, $nameParts[1].'.'.$ext]);
    }

    public function addAssetsForRenderNodeAndType(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        string $ext,
        ?string $templateAbstractPath = null
    ): void {
        $pathInfo = pathinfo(
            $this->buildPublicAssetPathFromTemplateAbstractPath(
                $templateAbstractPath ?: $renderNode->getTemplateAbstractPath(),
                $ext
            )
        );

        $usage = $this->getName();
        $usageKebab = TextHelper::toKebab($usage);

        if (isset($renderPass->usagesConfig[$usage]['list'])) {
            foreach ($renderPass->usagesConfig[$usage]['list'] as $usageValue => $config) {
                $assetPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'.'.$usageKebab.'.'.$usageValue.'.'.$pathInfo['extension'];

                if ($asset = $this->createAssetIfExists(
                    $assetPath,
                    $renderNode
                )) {
                    $asset->usages[$usage] = $usageValue;
                }
            }
        }
    }

    protected function createAssetIfExists(
        string $pathInManifest,
        AbstractRenderNode $renderNode,
    ): ?Asset {
        if (!$this->assetsRegistryService->assetExists($pathInManifest)) {
            return null;
        }

        $realPath = $this->assetsRegistryService->getRealPath($pathInManifest);

        if (!$realPath) {
            throw new Exception('Unable to find asset "'.$pathInManifest.'" in manifest.');
        }

        $asset = new Asset(
            $pathInManifest,
            $this::getName(),
            $renderNode->getContextType()
        );

        $renderNode->assets[$asset->type][] = $asset;

        $this->assetsRegistryService->addAsset(
            $asset,
        );

        return $asset;
    }

    public function assetNeedsInitialRender(
        Asset $asset,
        RenderPass $renderPass,
    ): bool {
        $usage = $this->getName();
        // This is the base usage (i.e. default).
        return $asset->usages[$usage] == $renderPass->getUsage($usage);
    }

    protected function hasExtraSwitchableUsage(RenderPass $renderPass): bool
    {
        $usage = static::getName();
        foreach (($renderPass->usagesConfig[$usage]['list'] ?? []) as $scheme => $config) {
            // There is at least one other switchable usage different from default one.
            if (($config['allow_switch'] ?? false)
                && $scheme !== $renderPass->getUsage($usage)) {
                return true;
            }
        }

        return false;
    }

    public function canAggregateAsset(
        RenderPass $renderPass,
        Asset $asset
    ): bool {
        return (!$this->hasExtraSwitchableUsage($renderPass)) && $asset->isServerSideRendered();
    }
}