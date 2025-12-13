<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode\Traits;


use Wexample\SymfonyDesignSystem\Rendering\RenderNode\PageRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Rendering\Traits\WithRenderRequestId;

trait DesignSystemLayoutRenderNodeTrait {
    use DesignSystemRenderNodeTrait {
        DesignSystemRenderNodeTrait::init as initBase;
    }
    use WithRenderRequestId;

    public function __construct(
        protected readonly string $env
    ) {

    }

    protected function getPageClass():string
    {
        return PageRenderNode::class;
    }

    public function init(
        RenderPass $renderPass,
        string $view,
    ): void {
        $this->initBase($renderPass, $view);

        $this->setRenderRequestId(
            $renderPass->getRenderRequestId()
        );

        $renderPass->setCurrentContextRenderNode(
            $this
        );
    }

    public function toDesignSystemLayoutArray(): array
    {
        return [
            'env' => $this->env,
            'renderRequestId' => $this->renderRequestId
        ];
    }
}