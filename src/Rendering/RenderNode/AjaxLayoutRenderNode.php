<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Rendering\RenderNode\Traits\DesignSystemLayoutRenderNodeTrait;

class AjaxLayoutRenderNode extends \Wexample\WebRenderNode\Rendering\RenderNode\AjaxLayoutRenderNode
{
    use DesignSystemLayoutRenderNodeTrait;

    public array $vueTemplates = [];

    public function toArray(): array
    {
        return parent::toArray()
            + [
                'vueTemplates' => $this->vueTemplates,
            ];
    }
}
