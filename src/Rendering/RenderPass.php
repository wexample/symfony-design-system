<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Symfony\Component\HttpFoundation\Request;
use Wexample\SymfonyDesignSystem\Helper\ColorSchemeHelper;
use Wexample\SymfonyDesignSystem\Helper\PageHelper;
use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AjaxLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\InitialLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;

class RenderPass
{
    private string $currentRequestId;

    public InitialLayoutRenderNode|AjaxLayoutRenderNode $layoutRenderNode;

    protected array $contextRenderNodeRegistry = [];

    protected array $contextRenderNodeStack = [];

    protected AbstractRenderNode $renderDataContextCurrent;

    public array $registry = [
        RenderingHelper::CONTEXT_COMPONENT => [],
        RenderingHelper::CONTEXT_PAGE => [],
        RenderingHelper::CONTEXT_LAYOUT => [],
        RenderingHelper::CONTEXT_VUE => [],
    ];

    public string $pageName;

    public function __construct(
        public AdaptiveResponse $adaptiveResponse,
        private bool $enableAggregation,
        private Request $request,
        public bool $useJs,
        public string $view,
    ) {
        $this->pageName = PageHelper::pageNameFromPath($this->view);

        $this->createRenderRequestId();

        $this->adaptiveResponse->setRenderPass($this);
    }

    public function prepare(
        &$parameters,
        string $env,
    ) {
        $className = InitialLayoutRenderNode::class;

        if (AdaptiveResponse::OUTPUT_TYPE_RESPONSE_JSON === $this->adaptiveResponse->getOutputType()) {
            $className = AjaxLayoutRenderNode::class;
        }

        $this->layoutRenderNode = new $className(
            $this,
            $this->useJs,
            $env
        );

        // Add global variables for rendering.
        $parameters =
            [
                'document_head_title' => '@page::page_title',
                'document_head_title_args' => [],
                'layout_name' => null,
                'layout_color_scheme' => ColorSchemeHelper::SCHEME_DEFAULT,
                'layout_animation' => null,
                'layout_shape' => null,
                'layout_use_js' => $this->useJs,
                'page_name' => $this->pageName,
                'page_path' => $this->view,
                'page_title' => '@page::page_title',
                'render_pass' => $this,
                'request_uri' => $this->request->getRequestUri(),
            ] + $parameters;
    }

    public function registerRenderNode(
        AbstractRenderNode $renderNode
    ) {
        $this->registry[$renderNode->getContextType()][$renderNode->name] = $renderNode;
    }

    public function createRenderRequestId(): string
    {
        $this->currentRequestId = uniqid();

        return $this->getRenderRequestId();
    }

    public function getRenderRequestId(): string
    {
        return $this->currentRequestId;
    }

    public function getEnableAggregation(): bool
    {
        return $this->enableAggregation;
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
}
