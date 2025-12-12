<?php

namespace Wexample\SymfonyDesignSystem\Service\Usage\Traits;

use Exception;
use Wexample\Helpers\Helper\PathHelper;
use Wexample\Helpers\Helper\TextHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\Traits\DesignSystemRenderNodeTrait;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\WebRenderNode\Rendering\RenderNode\AbstractRenderNode;

trait DesignSystemUsageServiceTrait
{
    public function buildPublicAssetPathFromView(
        string $view,
        string $ext
    ): string
    {
        $nameParts = explode('/', $view);
        $bundle = array_shift($nameParts);

        return AssetsService::DIR_BUILD . PathHelper::join(array_merge([$bundle, $ext], $nameParts)) . '.' . $ext;
    }

    /**
     * @param RenderPass $renderPass
     * @param AbstractRenderNode|DesignSystemRenderNodeTrait $renderNode
     * @param string $ext
     * @param string $view
     * @return bool
     * @throws Exception
     */
    public function addAssetsForRenderNodeAndType(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        string $ext,
        string $view
    ): bool
    {
        $pathInfo = pathinfo(
            $this->buildPublicAssetPathFromView(
                $view,
                $ext
            )
        );

        $usage = $this->getName();
        $usageKebab = TextHelper::toKebab($usage);
        $hasAsset = false;

        if (isset($renderPass->usagesConfig[$usage]['list'])) {
            foreach ($renderPass->usagesConfig[$usage]['list'] as $usageValue => $config) {
                $assetPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.' . $usageKebab . '.' . $usageValue . '.' . $pathInfo['extension'];

                if ($asset = $this->createAssetIfExists(
                    $renderPass,
                    $assetPath,
                    $view,
                    $renderNode
                )) {
                    $hasAsset = true;
                    $asset->addUsageValue($usage, $usageValue);
                }
            }
        }

        return $hasAsset;
    }

    /**
     * @throws Exception
     */
    protected function createAssetIfExists(
        RenderPass $renderPass,
        string $pathInManifest,
        string $view,
        AbstractRenderNode $renderNode,
    ): ?Asset
    {
        $registry = $renderPass->getAssetsRegistry();

        if (!$registry->assetExists($pathInManifest)) {
            return null;
        }

        $realPath = $registry->getRealPath($pathInManifest);

        if (!$realPath) {
            throw new Exception('Unable to find realpath of asset "'
                . $pathInManifest . ', check build folder content or files permissions.');
        }

        $asset = new Asset(
            ltrim($registry->getBuiltPath($pathInManifest), '/'),
            $view,
            $this::getName(),
            $renderNode->getContextType()
        );

        $renderNode->assets[$asset->getType()][] = $asset;

        $registry->addAsset(
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
        return $asset->getUsages()[$usage] == $renderPass->getUsage($usage);
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
        return (! $this->hasExtraSwitchableUsage($renderPass)) && $asset->isServerSideRendered();
    }
}
