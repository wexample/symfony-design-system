<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Symfony\Component\HttpFoundation\Request;
use Wexample\SymfonyDesignSystem\Helper\ColorSchemeHelper;
use Wexample\SymfonyDesignSystem\Helper\PageHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\InitialLayoutRenderNode;

class RenderPass
{
    public InitialLayoutRenderNode $layoutRenderNode;
    public string $pageName;
    protected array $contextRenderNodeStack = [];

    public function __construct(
        public AdaptiveResponse $adaptiveResponse,
        private bool $enableAggregation,
        private Request $request,
        public bool $useJs,
        public string $view,
    ) {
        $this->pageName = PageHelper::pageNameFromPath($this->view);

    }

    public function prepare(
        &$parameters,
        string $env,
    ) {
        $className = InitialLayoutRenderNode::class;

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
                'layout_use_js' => $this->useJs,
                'page_name' => $this->pageName,

                'render_pass' => $this,
            ] + $parameters;
    }

    public function getEnableAggregation(): bool
    {
        return $this->enableAggregation;
    }

    public function setCurrentContextRenderNodeByTypeAndName(
        string $renderNodeType,
        string $renderNodeName
    ) {

    }

    public function revertCurrentContextRenderNode(): void
    {
        array_pop($this->contextRenderNodeStack);
    }
}
