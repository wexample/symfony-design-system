<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode\Traits;


use Wexample\SymfonyDesignSystem\Rendering\RenderNode\PageRenderNode;

trait DesignSystemLayoutRenderNodeTrait {
    use DesignSystemRenderNodeTrait;

    protected function getPageClass():string
    {
        return PageRenderNode::class;
    }
}