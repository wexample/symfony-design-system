<?php

namespace Wexample\SymfonyDesignSystem\Service\Usage\Traits;

use Exception;
use Wexample\Helpers\Helper\PathHelper;
use Wexample\Helpers\Helper\TextHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\Traits\DesignSystemRenderNodeTrait;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\WebRenderNode\Asset\Asset;
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
                    $asset->usages[$usage] = $usageValue;
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
    ): ?Asset {
        $registry = $renderPass->getAssetsRegistry();

        if (! $registry->assetExists($pathInManifest)) {
            return null;
        }

        $builtPath = $registry->getBuiltPath($pathInManifest);

        if (! $builtPath) {
            return null;
        }

        $asset = new Asset(
            ltrim($builtPath, '/'),
            $view,
            static::getName(),
            $renderNode->getContextType()
        );

        $renderNode->addAsset($asset);
        $registry->addAsset($asset);

        return $asset;
    }
}
