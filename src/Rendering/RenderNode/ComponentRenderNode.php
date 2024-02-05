<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use JetBrains\PhpStorm\Pure;
use Wexample\SymfonyDesignSystem\Helper\DomHelper;
use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

class ComponentRenderNode extends AbstractRenderNode
{
    protected const VAR_INIT_MODE = 'initMode';

    public array $assets = [];

    public ?string $body = null;

    #[Pure]
    public function __construct(
        protected RenderPass $renderPass,
        public string $initMode,
        public array $options = []
    ) {
        parent::__construct(
            $this->renderPass
        );
    }

    public function init(string $name)
    {
        parent::init($name);

        $this
            ->renderPass
            ->getCurrentContextRenderNode()
            ->components[] = $this;
    }

    public function getContextType(): string
    {
        return RenderingHelper::CONTEXT_COMPONENT;
    }

    public function renderCssClass(): string
    {
        return 'com-class-loaded '.$this->id;
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

    public function toRenderData(): array
    {
        return parent::toRenderData()
            + [
                'initMode' => $this->initMode,
                'options' => $this->options,
            ];
    }
}
