<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Service;

use Wexample\SymfonyDesignSystem\Rendering\AdaptiveResponse;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyDesignSystem\Service\Usage\DefaultAssetUsageService;
use Wexample\SymfonyDesignSystem\Service\Usage\ResponsiveAssetUsageService;
use Wexample\SymfonyTesting\Tests\AbstractSymfonyTestCase;

class AssetsServiceTest extends AbstractSymfonyTestCase
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
            AdaptiveResponse::OUTPUT_TYPE_RESPONSE_HTML,
            'test.html.twig'
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
            DefaultAssetUsageService::getName()
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
            ResponsiveAssetUsageService::getName()
        );

        $this->assertFalse($service->assetNeedsInitialRender(
            $asset,
            $renderPass,
        ));

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
}