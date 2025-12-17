<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Rendering\AssetsRegistry;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyDesignSystem\Twig\AssetsExtension;

class AssetsExtensionTest extends TestCase
{
    public function testFunctionsAndDelegation(): void
    {
        $service = $this->createMock(AssetsService::class);
        $extension = new AssetsExtension($service);

        $functions = $extension->getFunctions();
        $names = array_map(static fn (TwigFunction $f) => $f->getName(), $functions);
        $this->assertContains('assets_build_tags', $names);
        $this->assertContains('assets_registry', $names);

        $renderPass = new RenderPass('view', new AssetsRegistry(sys_get_temp_dir()));

        $service->expects($this->once())->method('buildTags')->with($renderPass)->willReturn(['ok']);
        $this->assertSame(['ok'], $extension->assetsBuildTags($renderPass));

        $registryArray = $extension->assetsRegistry($renderPass);
        $this->assertIsArray($registryArray);
    }
}
