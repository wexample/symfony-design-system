<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;

class PageRenderNode extends AbstractRenderNode
{
    public function getContextType(): string
    {
        return RenderingHelper::CONTEXT_PAGE;
    }
}
