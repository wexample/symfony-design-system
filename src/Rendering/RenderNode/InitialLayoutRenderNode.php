<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Rendering\RenderNode\Traits\DesignSystemLayoutRenderNodeTrait;


class InitialLayoutRenderNode extends \Wexample\WebRenderNode\Rendering\RenderNode\InitialLayoutRenderNode
{
    use DesignSystemLayoutRenderNodeTrait;

    public function toArray(): array
    {
        return parent::toArray()
            + $this->toDesignSystemLayoutArray();
    }
}
