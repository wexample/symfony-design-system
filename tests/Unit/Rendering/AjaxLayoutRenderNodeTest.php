<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Rendering;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AjaxLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Rendering\AssetsRegistry;

class AjaxLayoutRenderNodeTest extends TestCase
{
    public function testToArrayIncludesVueTemplatesAndDesignSystemFields(): void
    {
        $node = new AjaxLayoutRenderNode('dev');
        $node->setView('bundle/view');
        $node->vueTemplates = ['tpl1'];

        $renderPass = new RenderPass('bundle/view', new AssetsRegistry(sys_get_temp_dir()));
        $renderPass->setRenderRequestId('rid');
        $node->init($renderPass, 'bundle/view');

        $page = $node->createLayoutPageInstance();
        $page->setView('bundle/page');

        $array = $node->toArray();

        $this->assertSame('dev', $array['env']);
        $this->assertSame(['tpl1'], $array['vueTemplates']);
        $this->assertSame('bundle/page', $array['page']['view']);
    }
}
