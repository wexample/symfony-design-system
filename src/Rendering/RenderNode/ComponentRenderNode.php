<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Helper\DomHelper;
use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyHelpers\Helper\VariableHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;

class ComponentRenderNode extends AbstractRenderNode
{
    public function init(
        RenderPass $renderPass,
        string $name
    ): void
    {
        parent::init($renderPass, $name);

        $renderPass
            ->getCurrentContextRenderNode()
            ->components[] = $this;
    }

    public function getContextType(): string
    {
        return RenderingHelper::CONTEXT_COMPONENT;
    }

    public function renderTag(): string
    {
        return DomHelper::buildTag(
            'span',
            [
                // ID are not used as "id" html attribute,
                // as component may be embedded into a vue,
                // so replicated multiple times.
                VariableHelper::CLASS_VAR => 'com-init '.$this->id,
            ]
        );
    }
}
