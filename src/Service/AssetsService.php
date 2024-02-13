<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Psr\Cache\InvalidArgumentException;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\Usage\AbstractAssetUsageService;
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
        DefaultAssetUsageService $defaultAssetUsageService,
        ResponsiveAssetUsageService $responsiveAssetUsageService
    ) {
        foreach ([$defaultAssetUsageService, $responsiveAssetUsageService] as $usage) {
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
        string $assetType = null
    ): array {
        $assets = [];

        /** @var AbstractRenderNode $renderNode */
        foreach ($renderPass->registry[$contextType] as $renderNode) {
            $assets = array_merge(
                $assets,
                $renderNode->assets[$assetType]
            );
        }

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
}
