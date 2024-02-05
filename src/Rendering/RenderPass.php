<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Wexample\SymfonyDesignSystem\Helper\PageHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AjaxLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\InitialLayoutRenderNode;

class RenderPass
{
    public InitialLayoutRenderNode|AjaxLayoutRenderNode $layoutRenderNode;

    public string $pageName;

    public function __construct(
        string $outputType,
        public string $view,
    ) {
        $this->pageName = PageHelper::pageNameFromPath($this->view);

        $className = InitialLayoutRenderNode::class;

        if (AdaptiveResponse::OUTPUT_TYPE_RESPONSE_JSON === $outputType) {
            $className = AjaxLayoutRenderNode::class;
        }

        $this->layoutRenderNode = new $className(
            $this,
        );
    }

    public function getRenderParameters(): array
    {
        return [
            'document_head_title' => '@page::page_title',
            'page_name' => $this->pageName,
        ];
    }
}
