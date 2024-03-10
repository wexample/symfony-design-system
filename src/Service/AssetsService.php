<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpKernel\KernelInterface;
use Wexample\SymfonyDesignSystem\Helper\TemplateHelper;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\AssetTag;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\Usage\AbstractAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\AnimationsAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\ColorSchemeAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\DefaultAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\FontsAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\MarginsAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\ResponsiveAssetUsageService;
use Wexample\SymfonyHelpers\Helper\BundleHelper;

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
        AnimationsAssetUsageService $animationsAssetUsageService,
        ColorSchemeAssetUsageService $colorSchemeAssetUsageService,
        DefaultAssetUsageService $defaultAssetUsageService,
        MarginsAssetUsageService $marginsAssetUsageService,
        ResponsiveAssetUsageService $responsiveAssetUsageService,
        FontsAssetUsageService $fontsAssetUsageService,
        readonly protected KernelInterface $kernel,
        readonly protected AssetsAggregationService $assetsAggregationService,
        readonly protected AssetsRegistryService $assetsRegistryService,
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
                     $marginsAssetUsageService,
                     $animationsAssetUsageService,
                     $fontsAssetUsageService,
                 ] as $usage) {
            $this->usages[$usage->getName()] = $usage;
        }
    }

    /**
     * @return AbstractAssetUsageService[]
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

    public function assetsDetect(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        ?string $templateAbstractPath = null
    ): void {
        if ($templateAbstractPath) {
            $abstractPaths = [$templateAbstractPath];
        } else {
            $abstractPaths = [];
            foreach ($renderNode->getInheritanceStack() as $templateName) {
                $abstractPaths[] = $this->buildTemplateAbstractPathFromTemplateName($templateName);
            }
        }

        foreach (Asset::ASSETS_EXTENSIONS as $ext) {
            foreach ($this->usages as $usage) {
                // i.e. only first css or js needed for the given usage,
                // inheritance is managed into asset.
                $usageFoundForType = false;

                foreach ($abstractPaths as $templateAbstractPath) {
                    if (!$usageFoundForType && $usage->addAssetsForRenderNodeAndType(
                            $renderPass,
                            $renderNode,
                            $ext,
                            $templateAbstractPath
                        )) {
                        $usageFoundForType = true;
                    }
                }
            }
        }
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

    public function assetNeedsInitialRender(
        Asset $asset,
        RenderPass $renderPass,
    ): bool {
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
    ): array {
        $usages = $this->getAssetsUsages();
        $tags = array_fill_keys(array_keys($usages), []);
        $contexts = Asset::CONTEXTS;
        $registry = $this->assetsRegistryService->getRegistry();

        foreach ($usages as $usageName => $usageManager) {
            foreach (Asset::ASSETS_EXTENSIONS as $type) {
                foreach ($contexts as $context) {
                    /** @var Asset $asset */
                    foreach ($registry[$type] as $asset) {
                        if ($asset->getUsage() == $usageName && $asset->getContext() == $context) {
                            if ($this->assetNeedsInitialRender(
                                $asset,
                                $renderPass,
                            )) {
                                $tag = new AssetTag($asset);

                                $asset->setServerSideRendered();

                                $tag->setCanAggregate(
                                    $usageManager->canAggregateAsset(
                                        $renderPass,
                                        $asset
                                    )
                                );

                                $tags[$usageName][$type][$context][] = $tag;
                            }
                        }
                    }

                    if (empty($tags[$usageName][$type])) {
                        $tag = new AssetTag();
                        $tag->setId($type.'-'.$usageName.'-'.$context.'-placeholder');
                        $tag->setPath(null);
                        $tag->setUsageName($usageName);
                        $tag->setContext($context);
                        $tags[$usageName][$type][$context][] = $tag;
                    }
                }
            }
        }

        $tag = new AssetTag();
        $tag->setCanAggregate(true);
        $tag->setPath('build/runtime.js');
        $tag->setId('javascript-runtime');

        $tags['extra'][Asset::EXTENSION_JS]['runtime'][] = $tag;

        if ($renderPass->enableAggregation) {
            return $this->assetsAggregationService->buildAggregatedTags(
                $this->buildTemplateAbstractPathFromTemplateName($renderPass->getView()),
                $tags,
            );
        }

        return $tags;
    }

    public function buildTemplateAbstractPathFromTemplateName(string $templateName): string
    {
        if (str_ends_with($templateName, TemplateHelper::TEMPLATE_FILE_EXTENSION)) {
            $templateName = substr(
                $templateName,
                0,
                -strlen(TemplateHelper::TEMPLATE_FILE_EXTENSION)
            );
        }

        $layoutNameParts = explode('/', $templateName);
        $bundleName = ltrim(current($layoutNameParts), '@');
        array_shift($layoutNameParts);
        $bundles = $this->kernel->getBundles();

        $nameRight = '::'.implode('/', $layoutNameParts);

        // This is a bundle alias.
        if (isset($bundles[$bundleName])) {
            $bundle = $this->kernel->getBundle($bundleName);
            return BundleHelper::getBundleCssAlias($bundle::class).$nameRight;
        }

        return 'app'.$nameRight;
    }
}
