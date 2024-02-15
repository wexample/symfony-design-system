<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AjaxLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\InitialLayoutRenderNode;

class RenderPass
{
    public InitialLayoutRenderNode|AjaxLayoutRenderNode $layoutRenderNode;

    protected array $contextRenderNodeRegistry = [];

    protected array $contextRenderNodeStack = [];

    public string $colorScheme;

    public array $colorSchemes = [];

    public array $displayBreakpoints = [];

    public array $registry = [
        RenderingHelper::CONTEXT_COMPONENT => [],
        RenderingHelper::CONTEXT_PAGE => [],
        RenderingHelper::CONTEXT_LAYOUT => [],
        RenderingHelper::CONTEXT_VUE => [],
    ];

    private bool $useJs = true;

    public function __construct(
        string $outputType,
        public string $view,
    ) {
        $className = InitialLayoutRenderNode::class;

        if (AdaptiveResponse::OUTPUT_TYPE_RESPONSE_JSON === $outputType) {
            $className = AjaxLayoutRenderNode::class;
        }

        $this->layoutRenderNode = new $className(
            $this,
        );
    }

    public function registerRenderNode(
        AbstractRenderNode $renderNode
    ) {
        $this->registry[$renderNode->getContextType()][$renderNode->name] = $renderNode;
    }

    public function getRenderParameters(): array
    {
        return [
            'document_head_title' => '@page::page_title',
        ];
    }

    public function registerContextRenderNode(
        AbstractRenderNode $renderNode
    ) {
        $this->contextRenderNodeRegistry[$renderNode->getContextRenderNodeKey()] = $renderNode;
    }

    public function setCurrentContextRenderNode(
        AbstractRenderNode $renderNode
    ) {
        $this->setCurrentContextRenderNodeByTypeAndName(
            $renderNode->getContextType(),
            $renderNode->name
        );
    }

    public function setCurrentContextRenderNodeByTypeAndName(
        string $renderNodeType,
        string $renderNodeName
    ) {
        $key = RenderingHelper::buildRenderContextKey(
            $renderNodeType,
            $renderNodeName
        );

        $this->contextRenderNodeStack[] = $this->contextRenderNodeRegistry[$key];
    }

    public function getCurrentContextRenderNode(): ?AbstractRenderNode
    {
        return empty($this->contextRenderNodeStack) ? null : end($this->contextRenderNodeStack);
    }

    public function revertCurrentContextRenderNode(): void
    {
        array_pop($this->contextRenderNodeStack);
    }

    public function isUseJs(): bool
    {
        return $this->useJs;
    }

    public function setUseJs(bool $useJs): void
    {
        $this->useJs = $useJs;
    }
}
