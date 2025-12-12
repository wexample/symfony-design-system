<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

abstract class AbstractLayoutRenderNode extends AbstractRenderNode
{
    public function createLayoutPageInstance(): PageRenderNode
    {
        $this->page = new PageRenderNode();

        return $this->page;
    }
}
