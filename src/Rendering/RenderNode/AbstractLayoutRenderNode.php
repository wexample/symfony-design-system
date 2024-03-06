<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;

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
}
