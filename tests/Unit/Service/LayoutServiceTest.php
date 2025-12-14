<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Service;

use PHPUnit\Framework\MockObject\MockObject;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Rendering\AssetsRegistry;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\InitialLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyDesignSystem\Service\LayoutServiceAbstract;
use Wexample\SymfonyDesignSystem\Service\PageServiceAbstract;
use Wexample\SymfonyTranslations\Translation\Translator;
use Wexample\SymfonyTesting\Tests\AbstractSymfonyKernelTestCase;

class LayoutServiceTest extends AbstractSymfonyKernelTestCase
{
    public function testLayoutInitialInitDelegatesToLayoutInit(): void
    {
        $renderPass = $this->createRenderPass();

        /** @var LayoutServiceAbstract&MockObject $service */
        $service = $this->getMockBuilder(LayoutServiceAbstract::class)
            ->setConstructorArgs([
                $this->createStub(AssetsService::class),
                $this->createStub(PageServiceAbstract::class),
                $this->createStub(Translator::class),
            ])
            ->onlyMethods(['layoutInit'])
            ->getMock();

        $service->expects($this->once())->method('layoutInit')->with($renderPass);

        $service->layoutInitialInit(
            $this->createStub(Environment::class),
            $renderPass
        );
    }

    public function testLayoutInitInitializesLayoutAndPage(): void
    {
        $assetsService = $this->createMock(AssetsService::class);
        $pageService = $this->createMock(PageServiceAbstract::class);
        $translator = $this->createMock(Translator::class);

        $renderPass = $this->createRenderPass();
        $layoutRenderNode = new InitialLayoutRenderNode('test');
        $layoutRenderNode->setView('bundle/layout');
        $renderPass->setLayoutRenderNode($layoutRenderNode);

        $assetsService
            ->expects($this->once())
            ->method('assetsDetect')
            ->with(
                $renderPass,
                $layoutRenderNode,
            );

        $translator
            ->expects($this->once())
            ->method('setDomainFromTemplatePath')
            ->with(
                $layoutRenderNode->getContextType(),
                $layoutRenderNode->getView()
            );

        $pageService
            ->expects($this->once())
            ->method('pageInit')
            ->with(
                $renderPass,
                $this->isInstanceOf(\Wexample\WebRenderNode\Rendering\RenderNode\PageRenderNode::class),
                $renderPass->getView()
            );

        $service = new LayoutServiceAbstract(
            $assetsService,
            $pageService,
            $translator
        );

        $service->layoutInit($renderPass);
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
