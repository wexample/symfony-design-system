<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AjaxLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\InitialLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Service\Usage\ResponsiveAssetUsageService;

class RenderPass
{
    public InitialLayoutRenderNode|AjaxLayoutRenderNode $layoutRenderNode;

    protected array $contextRenderNodeRegistry = [];

    protected array $contextRenderNodeStack = [];

    public array $usagesConfig = [];

    public ?bool $enableAggregation = null;

    private bool $debug = false;

    /**
     * @var array<string|null>
     */
    public array $usages = [];

    public array $registry = [
        RenderingHelper::CONTEXT_COMPONENT => [],
        RenderingHelper::CONTEXT_PAGE => [],
        RenderingHelper::CONTEXT_LAYOUT => [],
        RenderingHelper::CONTEXT_VUE => [],
    ];

    private bool $useJs = true;

    public function __construct(
        string $outputType,
        readonly private string $view,
    ) {
        $className = InitialLayoutRenderNode::class;

        if (AdaptiveResponse::OUTPUT_TYPE_RESPONSE_JSON === $outputType) {
            $className = AjaxLayoutRenderNode::class;
        }

        $this->layoutRenderNode = new $className();
    }

    public function registerRenderNode(
        AbstractRenderNode $renderNode
    ) {
        $this->registry[$renderNode->getContextType()][$renderNode->getTemplateAbstractPath()] = $renderNode;
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
            $renderNode->getTemplateAbstractPath()
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

    public function getView(): string
    {
        return $this->view;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }
}
