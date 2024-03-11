<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;

abstract class AbstractLayoutRenderNode extends AbstractRenderNode
{
    public PageRenderNode $page;

    public function getContextType(): string
    {
        return RenderingHelper::CONTEXT_LAYOUT;
    }

    public function toRenderData(): array
    {
        return parent::toRenderData()
            + $this->serializeVariables([
                'page',
            ]);
    }

    public function init(
        RenderPass $renderPass,
        string $templateName,
        string $name,
    ): void {
        parent::init($renderPass, $templateName, $name);

        $renderPass->setCurrentContextRenderNode(
            $this
        );
    }
}
