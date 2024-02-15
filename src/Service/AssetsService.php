<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Psr\Cache\InvalidArgumentException;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\Usage\AbstractAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\ColorSchemeAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\DefaultAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\ResponsiveAssetUsageService;
use function array_merge;

class AssetsService
{
    /**
     * @var array|Asset[][]
     */
    public const ASSETS_DEFAULT_EMPTY = [
        Asset::EXTENSION_CSS => [],
        Asset::EXTENSION_JS => [],
    ];

    /**
     * @var array<AbstractAssetUsageService>
     */
    private array $usages;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        ColorSchemeAssetUsageService $colorSchemeAssetUsageService,
        DefaultAssetUsageService $defaultAssetUsageService,
        ResponsiveAssetUsageService $responsiveAssetUsageService
    ) {
        foreach ([
                     // Order is important, it defines the order the assets
                     // i.e. the order of CSS loading, so responsive or
                     // color schemes should be loaded after base ones.
                     // The same order should also be used in frontend
                     // to preserve order during dynamic assets loading.
                     $defaultAssetUsageService,
                     $colorSchemeAssetUsageService,
                     $responsiveAssetUsageService,
                 ] as $usage) {
            $this->usages[$usage->getName()] = $usage;
        }
    }

    public function assetsDetect(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
    ): void {
        foreach (Asset::ASSETS_EXTENSIONS as $ext) {
            foreach ($this->usages as $usage) {
                $usage->addAssetsForRenderNodeAndType(
                    $renderPass,
                    $renderNode,
                    $ext
                );
            }
        }
    }

    public function assetsFiltered(
        RenderPass $renderPass,
        string $contextType,
        string $usage,
        string $assetType = null
    ): array {
        $assets = [];

        /** @var AbstractRenderNode $renderNode */
        foreach ($renderPass->registry[$contextType] as $renderNode) {
            foreach ($renderNode->assets[$assetType] as $asset) {
                if ($asset->getUsage() === $usage) {
                    $assets[] = $asset;
                }
            }
        }

        return $this->sortAssets($assets);
    }

    /**
     * Sort assets by usage.
     * @param array<Asset> $assets
     * @return array<Asset>
     */
    protected function sortAssets(array $assets): array
    {
        usort($assets, function(
            Asset $a,
            Asset $b
        ) {
            $orderA = array_search($a->getUsage(), $this->usages);
            $orderB = array_search($b->getUsage(), $this->usages);

            if ($orderA === $orderB) {
                return 0;
            }

            return $orderA < $orderB ? -1 : 1;
        });

        return $assets;
    }

    public function assetIsReadyForRender(
        Asset $asset,
        RenderPass $renderPass,
    ): bool {
        return $this->usages[$asset->getUsage()]->isAssetReadyForServerSideRendering(
            $asset,
            $renderPass
        );
    }

    public function getAssetsUsages(): array
    {
        return $this->usages;
    }
}
