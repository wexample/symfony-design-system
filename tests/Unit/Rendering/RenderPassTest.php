<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Rendering;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\AssetsRegistry;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Helper\RenderingHelper as DsRenderingHelper;
use Wexample\WebRenderNode\Helper\RenderingHelper as BaseRenderingHelper;
use Wexample\WebRenderNode\Rendering\RenderNode\AbstractRenderNode;

class RenderPassTest extends TestCase
{
    public function testContextRenderNodeStackPushPop(): void
    {
        $registry = new AssetsRegistry(sys_get_temp_dir());
        $renderPass = new RenderPass('view', $registry);

        $nodeA = $this->createConfiguredMock(AbstractRenderNode::class, [
            'getContextType' => BaseRenderingHelper::CONTEXT_LAYOUT,
            'getView' => 'view-a',
            'getContextRenderNodeKey' => DsRenderingHelper::buildRenderContextKey(BaseRenderingHelper::CONTEXT_LAYOUT, 'view-a'),
        ]);

        $nodeB = $this->createConfiguredMock(AbstractRenderNode::class, [
            'getContextType' => BaseRenderingHelper::CONTEXT_LAYOUT,
            'getView' => 'view-b',
            'getContextRenderNodeKey' => DsRenderingHelper::buildRenderContextKey(BaseRenderingHelper::CONTEXT_LAYOUT, 'view-b'),
        ]);

        // Register nodes so lookups succeed.
        $renderPass->registerContextRenderNode($nodeA);
        $renderPass->registerContextRenderNode($nodeB);

        $renderPass->setCurrentContextRenderNodeByTypeAndName(
            BaseRenderingHelper::CONTEXT_LAYOUT,
            'view-a'
        );
        $renderPass->setCurrentContextRenderNodeByTypeAndName(
            BaseRenderingHelper::CONTEXT_LAYOUT,
            'view-b'
        );

        $this->assertSame($nodeB, $renderPass->getCurrentContextRenderNode());

        $renderPass->revertCurrentContextRenderNode();
        $this->assertSame($nodeA, $renderPass->getCurrentContextRenderNode());

        $renderPass->revertCurrentContextRenderNode();
        $this->assertNull($renderPass->getCurrentContextRenderNode());
    }
}
