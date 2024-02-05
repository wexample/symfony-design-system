<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

class AjaxLayoutRenderNode extends AbstractLayoutRenderNode
{
    public array $vueTemplates = [];

    public function toRenderData(): array
    {
        return parent::toRenderData() + [
                'vueTemplates' => $this->vueTemplates,
            ];
    }
}
