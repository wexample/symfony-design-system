<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Rendering\Traits\WithRenderRequestId;

abstract class AbstractLayoutRenderNode extends AbstractRenderNode
{
    use WithRenderRequestId;

    public PageRenderNode $page;

    public function getContextType(): string
    {
        return RenderingHelper::CONTEXT_LAYOUT;
    }

    public function toRenderData(): array
    {
        return parent::toRenderData()
            + $this->serializeVariables([
                'renderRequestId',
                'page'
            ])
            + ['templates' => $this->getComponentsTemplates()];
    }

    public function init(
        RenderPass $renderPass,
        string $templateName,
        string $name,
    ): void {
        parent::init($renderPass, $templateName, $name);

        $this->setRenderRequestId(
            $renderPass->getRenderRequestId()
        );

        $renderPass->setCurrentContextRenderNode(
            $this
        );
    }

    public function createLayoutPageInstance(): PageRenderNode
    {
        $this->page = new PageRenderNode();

        return $this->page;
    }
}
