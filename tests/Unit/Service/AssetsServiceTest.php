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

    public function testAssetsDetectKeepsRegistryEmptyWhenNoManifest()
    {
        /** @var AssetsService $service */
        $service = $this->getTestService();

        $renderPass = new RenderPass(
            'bundle/view',
            new AssetsRegistry(sys_get_temp_dir())
        );

        $renderNode = new class extends AbstractRenderNode {
            public function __construct()
            {
                $this->setView('bundle/view');
            }

            public function getContextType(): string
            {
                return Asset::CONTEXT_PAGE;
            }

            public function getInheritanceStack(): array
            {
                return [$this->getView()];
            }
        };

        $service->assetsDetect($renderPass, $renderNode);

        $this->assertSame([], $renderPass->getAssetsRegistry()->getRegistry());
    }

    public function testBuildTagsCreatesAssetTagsAndPlaceholders()
    {
        /** @var AssetsService $service */
        $service = $this->getTestService();

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
        $renderPass->usagesConfig[DefaultAssetUsageService::getName()]['list'] = [];

        $tags = $service->buildTags($renderPass);

        $this->assertArrayHasKey(Asset::EXTENSION_CSS, $tags);
        $this->assertArrayHasKey(Asset::CONTEXT_LAYOUT, $tags[Asset::EXTENSION_CSS]);
        $this->assertNotEmpty($tags[Asset::EXTENSION_JS]['runtime']['extra']);

        $defaultTags = $tags[Asset::EXTENSION_CSS][Asset::CONTEXT_LAYOUT][DefaultAssetUsageService::getName()];
        $this->assertSame('test.css', $defaultTags[0]->getPath());
    }
}
