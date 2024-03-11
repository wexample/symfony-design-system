<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;

class PageRenderNode extends AbstractRenderNode
{
    public ?string $body = null;

    public bool $isInitialPage = false;

    public function getContextType(): string
    {
        return RenderingHelper::CONTEXT_PAGE;
    }

    public function toRenderData(): array
    {
        return parent::toRenderData()
            + $this->serializeVariables([
                'body',
                'isInitialPage',
            ]);
    }
}
