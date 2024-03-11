<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;

abstract class AbstractLayoutRenderNode extends AbstractRenderNode
{
    public PageRenderNode $page;

    public array $events = [];

    public ?string $templates = null;

    public function __construct(
        protected RenderPass $renderPass,
        public bool $useJs
    ) {
        // No render context for layout.
        parent::__construct(
            $this->renderPass
        );
    }

    public function init(
        RenderPass $renderPass,
        string $name,
    ): void {
        parent::init(
            $renderPass,
            $name
        );

        $this->page = new PageRenderNode();
    }

    public function getContextType(): string
    {
        return RenderingHelper::CONTEXT_LAYOUT;
    }

    public function toRenderData(): array
    {
        return parent::toRenderData()
            + $this->serializeVariables(['page'])
            + ['templates' => $this->getComponentsTemplates()];

    }
}
