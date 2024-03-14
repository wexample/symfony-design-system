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

    public function isUseJs(): bool
    {
        return $this->useJs;
    }

    public function setUseJs(bool $useJs): void
    {
        $this->useJs = $useJs;
    }

    public function getDisplayBreakpoints(): array
    {
        $usagesTypes = $this->usagesConfig[ResponsiveAssetUsageService::getName()]['list'];
        $breakpoints = [];

        foreach ($usagesTypes as $name => $config) {
            $breakpoints[$name] = $config['breakpoint'];
        }

        return $breakpoints;
    }

    public function getUsage(
        string $usageName,
    ): ?string {
        return $this->usages[$usageName];
    }

    public function setUsage(
        string $usageName,
        ?string $usageValue
    ): void {
        // Not found
        if (!isset($this->usagesConfig[$usageName])) {
            return;
        }

        $this->usages[$usageName] = $usageValue;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    public function setOutputType(string $type): self
    {
        $this->outputType = $type;

        return $this;
    }

    public function getOutputType(): string
    {
        return $this->outputType;
    }

    public function isJsonRequest(): bool
    {
        return self::OUTPUT_TYPE_RESPONSE_JSON === $this->getOutputType();
    }

    public function isHtmlRequest(): bool
    {
        return self::OUTPUT_TYPE_RESPONSE_HTML === $this->getOutputType();
    }

    public function getLayoutBase(): string
    {
        return $this->layoutBase;
    }

    public function setLayoutBase(string $layoutBase): void
    {
        $this->layoutBase = $layoutBase;
    }
}
