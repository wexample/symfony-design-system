<?php

namespace Wexample\SymfonyDesignSystem\Service;

use InvalidArgumentException;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\AssetTag;
use Wexample\SymfonyDesignSystem\Rendering\CssAssetTag;
use Wexample\SymfonyDesignSystem\Rendering\JsAssetTag;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\Traits\DesignSystemRenderNodeTrait;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\Usage\AnimationsAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\ColorSchemeAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\DefaultAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\FontsAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\MarginsAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\ResponsiveAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\Traits\DesignSystemUsageServiceTrait;
use Wexample\WebRenderNode\Asset\AssetManager;
use Wexample\WebRenderNode\Rendering\RenderNode\AbstractRenderNode;

class AssetsService extends AssetManager
{
    public const DIR_BUILD = 'build/';

    public const DIR_PUBLIC = 'public/';

    /**
     * @var array<DesignSystemUsageServiceTrait>
     */
    private array $usages;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        AnimationsAssetUsageService $animationsAssetUsageService,
        ColorSchemeAssetUsageService $colorSchemeAssetUsageService,
        DefaultAssetUsageService $defaultAssetUsageService,
        MarginsAssetUsageService $marginsAssetUsageService,
        ResponsiveAssetUsageService $responsiveAssetUsageService,
        FontsAssetUsageService $fontsAssetUsageService,
        protected readonly AssetsAggregationService $assetsAggregationService,
    )
    {
        foreach ([
                     // Order is important, it defines the order the assets
                     // i.e. the order of CSS loading, so responsive or
                     // color schemes should be loaded after base ones.
                     // The same order should also be used in frontend
                     // to preserve order during dynamic assets loading.
                     $defaultAssetUsageService,
                     $colorSchemeAssetUsageService,
                     $responsiveAssetUsageService,
                     $marginsAssetUsageService,
                     $animationsAssetUsageService,
                     $fontsAssetUsageService,
                 ] as $usage) {
            $this->usages[$usage->getName()] = $usage;
        }
    }

    /**
     * @return DesignSystemUsageServiceTrait[]
     */
    public static function getAssetsUsagesStatic(): array
    {
        return [
            AnimationsAssetUsageService::class,
            ColorSchemeAssetUsageService::class,
            DefaultAssetUsageService::class,
            MarginsAssetUsageService::class,
            ResponsiveAssetUsageService::class,
            FontsAssetUsageService::class,
        ];
    }

    /**
     * @param RenderPass $renderPass
     * @param AbstractRenderNode|DesignSystemRenderNodeTrait $renderNode
     * @param string|null $view
     * @return void
     */
    public function assetsDetect(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        ?string $view = null
    ): void
    {
        if ($view) {
            $views = [$view];
        } else {
            $views = $renderNode->getInheritanceStack();
        }

        foreach (Asset::ASSETS_EXTENSIONS as $ext) {
            foreach ($this->usages as $usage) {
                // i.e. only first css or js needed for the given usage,
                // inheritance is managed into asset.
                $usageFoundForType = false;

                foreach ($views as $view) {
                    if (!$usageFoundForType && $usage->addAssetsForRenderNodeAndType(
                            $renderPass,
                            $renderNode,
                            $ext,
                            $view
                        )) {
                        $usageFoundForType = true;
                    }
                }
            }
        }
    }

    public function assetNeedsInitialRender(
        Asset $asset,
        RenderPass $renderPass,
    ): bool
    {
        return $this->usages[$asset->getUsage()]->assetNeedsInitialRender(
            $asset,
            $renderPass
        );
    }

    public function getAssetsUsages(): array
    {
        return $this->usages;
    }

    public function buildTags(
        RenderPass $renderPass,
    ): array
    {
        $usages = $this->getAssetsUsages();
        $tags = [];
        $assetsRegistry = $renderPass->getAssetsRegistry();
        $registry = $assetsRegistry->getRegistry();

        $classes = [
          'css' => CssAssetTag::class,
          'js' => JsAssetTag::class
        ];

        // Ensure registry has entries for all asset types
        foreach (Asset::ASSETS_EXTENSIONS as $type) {
            if (!isset($registry[$type])) {
                $registry[$type] = [];
            }
            $tags[$type] = array_fill_keys(Asset::CONTEXTS, []);

            foreach (Asset::CONTEXTS as $context) {
                foreach ($usages as $usageName => $usageManager) {
                    /** @var Asset $asset */
                    foreach ($registry[$type] as $asset) {
                        if ($asset->getUsage() == $usageName && $asset->getContext() == $context) {
                            if ($this->assetNeedsInitialRender(
                                $asset,
                                $renderPass,
                            )) {
                                $tag = new ($classes[$type])($asset);

                                $asset->setServerSideRendered();

                                $tag->setCanAggregate(
                                    $usageManager->canAggregateAsset(
                                        $renderPass,
                                        $asset
                                    )
                                );

                                $tags[$type][$context][$usageName][] = $tag;
                            }
                        }
                    }

                    if (empty($tags[$type][$context][$usageName])) {
                        $tag = new ($classes[$type])();
                        $tag->setId($type . '-' . $usageName . '-' . $context . '-placeholder');
                        $tag->setPath(null);
                        $tag->setUsageName($usageName);
                        $tag->setContext($context);
                        $tags[$type][$context][$usageName][] = $tag;
                    }
                }
            }
        }

        $tag = new JsAssetTag();
        $tag->setCanAggregate(true);
        $tag->setPath('build/runtime.js');
        $tag->setId('javascript-runtime');
        $tag->setContext('extra');

        $tags[Asset::EXTENSION_JS]['runtime']['extra'][] = $tag;

        if ($renderPass->enableAggregation) {
            return $this->assetsAggregationService->buildAggregatedTags(
                $renderPass->getView(),
                $tags,
            );
        }

        return $tags;
    }
}
