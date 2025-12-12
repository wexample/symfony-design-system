<?php

namespace Wexample\SymfonyDesignSystem\Service;

use InvalidArgumentException;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\Traits\DesignSystemRenderNodeTrait;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\Usage\Traits\DesignSystemUsageServiceTrait;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\WebRenderNode\Asset\AssetManager;
use Wexample\WebRenderNode\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Service\Usage\AnimationsAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\ColorSchemeAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\DefaultAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\FontsAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\MarginsAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\ResponsiveAssetUsageService;

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
    ): void {
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
                    if (! $usageFoundForType && $usage->addAssetsForRenderNodeAndType(
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
}
