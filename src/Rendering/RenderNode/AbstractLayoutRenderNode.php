<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;

abstract class AbstractLayoutRenderNode extends AbstractRenderNode
{
    public PageRenderNode $page;

    public function init(
        string $name,
    ): void {
        parent::init(
            $name
        );

        $this->page = new PageRenderNode();
    }

    public function getContextType(): string
    {
        return RenderingHelper::CONTEXT_LAYOUT;
    }
}
