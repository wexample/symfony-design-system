<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Service\Usage;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\AssetsRegistry;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\Usage\FontsAssetUsageService;

class FontsAssetUsageServiceTest extends TestCase
{
    public function testAssetNeedsInitialRenderMatchesUsage(): void
    {
        $service = new FontsAssetUsageService();

        $renderPass = new RenderPass('view', new AssetsRegistry(sys_get_temp_dir()));
        $renderPass->usagesConfig = [
            FontsAssetUsageService::getName() => ['list' => []],
        ];
        $renderPass->setUsage(FontsAssetUsageService::getName(), 'default');

        $asset = new Asset('path.css', 'view', FontsAssetUsageService::getName(), Asset::CONTEXT_LAYOUT);
        $asset->addUsageValue(FontsAssetUsageService::getName(), 'default');

        $this->assertTrue($service->assetNeedsInitialRender($asset, $renderPass));

        // Mismatch should return false.
        $renderPass->setUsage(FontsAssetUsageService::getName(), 'other');
        $this->assertFalse($service->assetNeedsInitialRender($asset, $renderPass));
    }

    public function testCanAggregateAssetDependsOnSwitchableUsage(): void
    {
        $service = new FontsAssetUsageService();

        $renderPass = new RenderPass('view', new AssetsRegistry(sys_get_temp_dir()));
        $renderPass->usagesConfig = [
            FontsAssetUsageService::getName() => [
                'list' => [
                    'default' => ['allow_switch' => false],
                ],
            ],
        ];
        $renderPass->setUsage(FontsAssetUsageService::getName(), 'default');

        $asset = new Asset('path.css', 'view', FontsAssetUsageService::getName(), Asset::CONTEXT_LAYOUT);
        $asset->addUsageValue(FontsAssetUsageService::getName(), 'default');
        $asset->setServerSideRendered();

        $this->assertTrue($service->canAggregateAsset($renderPass, $asset));

        // Add a switchable usage to force hasExtraSwitchableUsage = true => cannot aggregate.
        $renderPass->usagesConfig[FontsAssetUsageService::getName()]['list']['alt'] = ['allow_switch' => true];
        $this->assertFalse($service->canAggregateAsset($renderPass, $asset));
    }
}
