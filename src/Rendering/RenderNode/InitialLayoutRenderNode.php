<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Rendering\RenderPass;

class InitialLayoutRenderNode extends AbstractLayoutRenderNode
{
    public function init(
        RenderPass $renderPass,
        string $name,
    ):void {
        parent::init(
            $renderPass,
            $name
        );

        $this->page->isInitialPage = true;

        $this->vars += [
            'displayBreakpoints' => $renderPass->displayBreakpoints,
        ];
    }
}
