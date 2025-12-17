<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Service\Usage;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\AssetsRegistry;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\Usage\FontsAssetUsageService;
use Wexample\WebRenderNode\Rendering\RenderNode\AbstractRenderNode;

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

    public function testCreateAssetIfExistsThrowsWhenRealPathMissing(): void
    {
        $service = new FontsAssetUsageService();

        $registry = $this->createStub(AssetsRegistry::class);
        $registry->method('assetExists')->willReturn(true);
        $registry->method('getRealPath')->willReturn(null);
        $registry->method('getBuiltPath')->willReturn('path.css');

        $renderPass = $this->createStub(RenderPass::class);
        $renderPass->method('getAssetsRegistry')->willReturn($registry);

        $renderNode = $this->createStub(AbstractRenderNode::class);
        $renderNode->method('getContextType')->willReturn(Asset::CONTEXT_LAYOUT);
        $renderNode->assets = [];

        $this->expectException(\Exception::class);
        $this->invokeCreateAssetIfExists($service, $renderPass, 'path.css', 'view', $renderNode);
    }

    /**
     * @param FontsAssetUsageService $service
     * @param RenderPass $renderPass
     * @param string $path
     * @param string $view
     * @param AbstractRenderNode $renderNode
     * @return mixed
     */
    private function invokeCreateAssetIfExists(
        FontsAssetUsageService $service,
        RenderPass $renderPass,
        string $path,
        string $view,
        AbstractRenderNode $renderNode
    ) {
        $ref = new \ReflectionMethod($service, 'createAssetIfExists');
        $ref->setAccessible(true);

        return $ref->invoke($service, $renderPass, $path, $view, $renderNode);
    }
}
