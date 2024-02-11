<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Helper\DomHelper;
use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

class ComponentRenderNode extends AbstractRenderNode
{
    public string $cssClassName;

    public function __construct(
        public string $initMode,
        public array $options = []
    ) {

    }

    public function init(
        RenderPass $renderPass,
        string $name
    ): void {
        parent::init($renderPass, $name);

        $this->cssClassName = DomHelper::buildCssClassName($this->id);

        $renderPass
            ->getCurrentContextRenderNode()
            ->components[] = $this;
    }

    public function getContextType(): string
    {
        return RenderingHelper::CONTEXT_COMPONENT;
    }

    public function renderCssClasses(): string
    {
        return 'com-class-loaded '.$this->cssClassName;
    }

    public function renderTag(): string
    {
        return DomHelper::buildTag(
            'span',
            [
                // ID are not used as "id" html attribute,
                // as component may be embedded into a vue,
                // so replicated multiple times.
                VariableHelper::CLASS_VAR => 'com-init '.$this->cssClassName,
            ]
        );
    }

    public function toRenderData(): array
    {
        return parent::toRenderData()
            + [
                'cssClassName' => $this->cssClassName,
                'initMode' => $this->initMode,
                'options' => $this->options,
            ];
    }
}
