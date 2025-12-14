<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Service;

use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\AssetsRegistry;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyDesignSystem\Service\Usage\DefaultAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\ResponsiveAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\AnimationsAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\ColorSchemeAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\FontsAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\MarginsAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\AssetsAggregationService;
use Wexample\SymfonyTesting\Tests\AbstractSymfonyKernelTestCase;
use Wexample\WebRenderNode\Rendering\RenderNode\AbstractRenderNode;

class AssetsServiceTest extends AbstractSymfonyKernelTestCase
{
    protected function getTestServiceClass(): string
    {
        return AssetsService::class;
    }

    protected function getTestService(): object
    {
        return self::getContainer()->get(
            $this->getTestServiceClass()
        );
    }

    public function testAssetIsReadyForRender()
    {
        $renderPass = new RenderPass(
            view: 'test',
            assetsRegistry: new AssetsRegistry(
                projectDir: self::getContainer()->getParameter('kernel.project_dir')
            )
        );

        $this->checkAssetIsReadyForRenderDefault($renderPass);
        $this->checkAssetIsReadyForRenderResponsive($renderPass);
    }

    private function checkAssetIsReadyForRenderDefault(RenderPass $renderPass)
    {
        /** @var AssetsService $service */
        $service = $this->getTestService();

        $asset = new Asset(
            'test.css',
            'test',
            DefaultAssetUsageService::getName(),
            'test'
        );

        $this->assertTrue($service->assetNeedsInitialRender(
            $asset,
            $renderPass,
        ));
    }


    private function checkAssetIsReadyForRenderResponsive(RenderPass $renderPass)
    {
        /** @var AssetsService $service */
        $service = $this->getTestService();

        $asset = new Asset(
            'test.css',
            'test',
            ResponsiveAssetUsageService::getName(),
            'test'
        );

        // Do not check needs initial render, as we are not sure of what we expect.

        // When JS is disabled, responsive will render css assets
        // with media query attributes as a fallback mechanism.
        $renderPass->setUseJs(false);
        $this->assertTrue($service->assetNeedsInitialRender(
            $asset,
            $renderPass,
        ));

        // Rollback
        $renderPass->setUseJs(true);
    }

    public function testGetAssetsUsagesStaticContainsAllUsages()
    {
        $this->assertSame(
            [
                AnimationsAssetUsageService::class,
                ColorSchemeAssetUsageService::class,
                DefaultAssetUsageService::class,
                MarginsAssetUsageService::class,
                ResponsiveAssetUsageService::class,
                FontsAssetUsageService::class,
            ],
            AssetsService::getAssetsUsagesStatic()
        );
    }

    public function testGetAssetsUsagesReturnsRegisteredUsages()
    {
        /** @var AssetsService $service */
        $service = $this->getTestService();

        $usages = $service->getAssetsUsages();

        $this->assertNotEmpty($usages);
        $this->assertArrayHasKey(DefaultAssetUsageService::getName(), $usages);
        $this->assertArrayHasKey(ResponsiveAssetUsageService::getName(), $usages);
    }

    public function testAssetsDetectInvokesUsageDetectionPerExtension()
    {
        $usageCalls = [];
        $usages = $this->createUsageMocks($usageCalls);

        $service = new AssetsService(
            $usages[AnimationsAssetUsageService::class],
            $usages[ColorSchemeAssetUsageService::class],
            $usages[DefaultAssetUsageService::class],
            $usages[MarginsAssetUsageService::class],
            $usages[ResponsiveAssetUsageService::class],
            $usages[FontsAssetUsageService::class],
            $this->createMock(AssetsAggregationService::class)
        );

        $renderPass = new RenderPass(
            'bundle/view',
            new AssetsRegistry(sys_get_temp_dir())
        );

        $renderNode = $this->createMock(AbstractRenderNode::class);
        $renderNode->method('getInheritanceStack')->willReturn(['bundle/view']);

        $service->assetsDetect($renderPass, $renderNode);

        $expectedCalls = count(Asset::ASSETS_EXTENSIONS) * count($usages);
        $this->assertCount($expectedCalls, $usageCalls);
    }

    public function testBuildTagsCreatesAssetTagsAndPlaceholders()
    {
        $usageCalls = [];
        $usages = $this->createUsageMocks($usageCalls, defaultNeedsRender: true);

        $aggregation = $this->createMock(AssetsAggregationService::class);
        $aggregation->expects($this->never())->method('buildAggregatedTags');

        $service = new AssetsService(
            $usages[AnimationsAssetUsageService::class],
            $usages[ColorSchemeAssetUsageService::class],
            $usages[DefaultAssetUsageService::class],
            $usages[MarginsAssetUsageService::class],
            $usages[ResponsiveAssetUsageService::class],
            $usages[FontsAssetUsageService::class],
            $aggregation
        );

        $assetsRegistry = new AssetsRegistry(sys_get_temp_dir());
        $assetsRegistry->addAsset(
            new Asset(
                'test.css',
                'bundle/view',
                DefaultAssetUsageService::getName(),
                Asset::CONTEXT_LAYOUT
            )
        );

        $renderPass = new RenderPass(
            'bundle/view',
            $assetsRegistry
        );
        $renderPass->enableAggregation = false;

        $tags = $service->buildTags($renderPass);

        $this->assertArrayHasKey(Asset::EXTENSION_CSS, $tags);
        $this->assertArrayHasKey(Asset::CONTEXT_LAYOUT, $tags[Asset::EXTENSION_CSS]);
        $this->assertNotEmpty($tags[Asset::EXTENSION_JS]['runtime']['extra']);

        $defaultTags = $tags[Asset::EXTENSION_CSS][Asset::CONTEXT_LAYOUT][DefaultAssetUsageService::getName()];
        $this->assertSame('test.css', $defaultTags[0]->getPath());
    }

    /**
     * @return array<string, object>
     */
    private function createUsageMocks(array &$calls, bool $defaultNeedsRender = false): array
    {
        $usages = [];

        foreach ([
                     AnimationsAssetUsageService::class => AnimationsAssetUsageService::getName(),
                     ColorSchemeAssetUsageService::class => ColorSchemeAssetUsageService::getName(),
                     DefaultAssetUsageService::class => DefaultAssetUsageService::getName(),
                     MarginsAssetUsageService::class => MarginsAssetUsageService::getName(),
                     ResponsiveAssetUsageService::class => ResponsiveAssetUsageService::getName(),
                     FontsAssetUsageService::class => FontsAssetUsageService::getName(),
                 ] as $class => $name) {
            $mock = $this->createMock($class);

            $mock->method('getName')->willReturn($name);

            $mock->method('addAssetsForRenderNodeAndType')
                ->willReturnCallback(function () use (&$calls, $name) {
                    $calls[] = $name;

                    return false;
                });

            $mock->method('assetNeedsInitialRender')
                ->willReturn($defaultNeedsRender && $class === DefaultAssetUsageService::class);

            $mock->method('canAggregateAsset')->willReturn(true);

            $usages[$class] = $mock;
        }

        return $usages;
    }
}
