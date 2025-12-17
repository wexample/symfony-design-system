<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Rendering;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\AssetsRegistry;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\WebRenderNode\Helper\RenderingHelper as BaseRenderingHelper;
use Wexample\WebRenderNode\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Helper\RenderingHelper as DsRenderingHelper;

class RenderPassTest extends TestCase
{
    public function testContextRenderNodeStackPushPop(): void
    {
        $registry = new AssetsRegistry(sys_get_temp_dir());
        $renderPass = new RenderPass('view', $registry);

        $nodeA = new class extends AbstractRenderNode {
            public function getContextType(): string
            {
                return BaseRenderingHelper::CONTEXT_LAYOUT;
            }

            public function getContextRenderNodeKey(): string
            {
                return DsRenderingHelper::buildRenderContextKey($this->getContextType(), $this->getView());
            }
        };
        $nodeA->setView('view-a');

        $nodeB = new class extends AbstractRenderNode {
            public function getContextType(): string
            {
                return BaseRenderingHelper::CONTEXT_LAYOUT;
            }

            public function getContextRenderNodeKey(): string
            {
                return DsRenderingHelper::buildRenderContextKey($this->getContextType(), $this->getView());
            }
        };
        $nodeB->setView('view-b');

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

    public function testFlagsAndOutputTypeHelpers(): void
    {
        $registry = new AssetsRegistry(sys_get_temp_dir());
        $renderPass = new RenderPass('view', $registry);

        $this->assertFalse($renderPass->isDebug());
        $renderPass->setDebug(true);
        $this->assertTrue($renderPass->isDebug());

        // Default is HTML.
        $this->assertTrue($renderPass->isHtmlRequest());
        $this->assertFalse($renderPass->isJsonRequest());

        $renderPass->setOutputType(RenderPass::OUTPUT_TYPE_RESPONSE_JSON);
        $this->assertTrue($renderPass->isJsonRequest());
        $this->assertFalse($renderPass->isHtmlRequest());

        $this->assertTrue($renderPass->isUseJs());
        $renderPass->setUseJs(false);
        $this->assertFalse($renderPass->isUseJs());

        // Layout base getter/setter.
        $this->assertSame(RenderPass::BASE_DEFAULT, $renderPass->getLayoutBase());
        $renderPass->setLayoutBase(RenderPass::BASE_MODAL);
        $this->assertSame(RenderPass::BASE_MODAL, $renderPass->getLayoutBase());
    }

    public function testRegisterRenderNodeCreatesContextBucket(): void
    {
        $registry = new AssetsRegistry(sys_get_temp_dir());
        $renderPass = new RenderPass('view', $registry);

        $node = new class extends AbstractRenderNode {
            public function getContextType(): string
            {
                return BaseRenderingHelper::CONTEXT_VUE;
            }
        };
        $node->setView('foo');

        $renderPass->registerRenderNode($node);

        $ref = new \ReflectionProperty(RenderPass::class, 'registry');
        $ref->setAccessible(true);
        $registryProperty = $ref->getValue($renderPass);

        $this->assertSame($node, $registryProperty[BaseRenderingHelper::CONTEXT_VUE]['foo']);
    }

    public function testRegisterRenderNodeCreatesUnknownContextBucket(): void
    {
        $registry = new AssetsRegistry(sys_get_temp_dir());
        $renderPass = new RenderPass('view', $registry);

        $node = new class extends AbstractRenderNode {
            public function getContextType(): string
            {
                return 'custom-context';
            }
        };
        $node->setView('bar');

        $renderPass->registerRenderNode($node);

        $ref = new \ReflectionProperty(RenderPass::class, 'registry');
        $ref->setAccessible(true);
        $registryProperty = $ref->getValue($renderPass);

        $this->assertSame($node, $registryProperty['custom-context']['bar']);
    }

    public function testSetUsageSkipsUnknownUsage(): void
    {
        $registry = new AssetsRegistry(sys_get_temp_dir());
        $renderPass = new RenderPass('view', $registry);

        // No config => should not set.
        $renderPass->setUsage('unknown', 'value');
        $this->assertArrayNotHasKey('unknown', $renderPass->usages);

        // With config => should set.
        $renderPass->usagesConfig = ['known' => ['list' => []]];
        $renderPass->setUsage('known', 'val');
        $this->assertSame('val', $renderPass->getUsage('known'));
    }
}
