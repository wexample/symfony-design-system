<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Rendering\AssetsRegistry;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Wexample\SymfonyDesignSystem\Twig\AdaptiveResponseExtension;

class AdaptiveResponseExtensionTest extends TestCase
{
    public function testFunctionsAndDelegation(): void
    {
        $service = $this->createMock(AdaptiveResponseService::class);
        $extension = new AdaptiveResponseExtension($service);

        $functions = $extension->getFunctions();
        $names = array_map(static fn (TwigFunction $f) => $f->getName(), $functions);
        $this->assertContains('adaptive_response_rendering_base_path', $names);

        $renderPass = new RenderPass('view', new AssetsRegistry(sys_get_temp_dir()));

        $service->expects($this->once())->method('getLayoutBasePath')->with($renderPass)->willReturn('path');
        $this->assertSame('path', $extension->adaptiveResponseRenderingBasePath($renderPass));
    }
}
