<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Service;

use PHPUnit\Framework\MockObject\MockObject;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\AssetsRegistry;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\PageRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyDesignSystem\Service\PageService;
use Wexample\SymfonyTranslations\Translation\Translator;
use Wexample\SymfonyTesting\Tests\AbstractSymfonyKernelTestCase;

class PageServiceTest extends AbstractSymfonyKernelTestCase
{
    public function testPageInitInitializesPageAndTranslator(): void
    {
        /** @var AssetsService&MockObject $assetsService */
        $assetsService = $this->createMock(AssetsService::class);
        $translator = $this->createMock(Translator::class);

        $renderPass = $this->createRenderPass();
        $page = new PageRenderNode();
        $page->setView('bundle/page');

        $assetsService
            ->expects($this->once())
            ->method('assetsDetect')
            ->with(
                $renderPass,
                $page
            );

        $translator
            ->expects($this->once())
            ->method('setDomainFromTemplatePath')
            ->with(
                $page->getContextType(),
                'bundle/page'
            );

        $service = new PageService($assetsService, $translator);

        $service->pageInit(
            $renderPass,
            $page,
            'bundle/page'
        );
    }

    private function createRenderPass(): RenderPass
    {
        $assetsRegistry = new AssetsRegistry(__DIR__.'/../../Fixtures/assets');

        return new RenderPass(
            'bundle/view',
            $assetsRegistry
        );
    }
}
