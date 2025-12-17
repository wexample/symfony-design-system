<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Rendering;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\InitialLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Rendering\AssetsRegistry;

class InitialLayoutRenderNodeTest extends TestCase
{
    public function testToArrayIncludesDesignSystemFields(): void
    {
        $node = new InitialLayoutRenderNode('dev');
        $node->setView('bundle/view');

        // Mimic init to populate renderRequestId and context registration.
        $renderPass = new RenderPass('bundle/view', new AssetsRegistry(sys_get_temp_dir()));
        $renderPass->setRenderRequestId('rid');
        $node->init($renderPass, 'bundle/view');

        $page = $node->createLayoutPageInstance();
        $page->setView('bundle/page');

        $array = $node->toArray();

        $this->assertSame('dev', $array['env']);
        $this->assertArrayHasKey('renderRequestId', $array);
        $this->assertSame('bundle/page', $array['page']['view']);
    }
}
