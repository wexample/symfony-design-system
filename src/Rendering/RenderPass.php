<?php

namespace Wexample\SymfonyDesignSystem\Rendering;


use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AjaxLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\InitialLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\Traits\WithRenderRequestId;
use Wexample\SymfonyDesignSystem\Service\Usage\ResponsiveAssetUsageService;
use Wexample\SymfonyHelpers\Helper\VariableHelper;
use Wexample\WebRenderNode\Rendering\RenderNode\AbstractLayoutRenderNode;
use Wexample\WebRenderNode\Rendering\RenderNode\AbstractRenderNode;
use Wexample\WebRenderNode\Rendering\Traits\WithView;

class RenderPass
{
    use WithView;
    use WithRenderRequestId;

    public const BASE_DEFAULT = VariableHelper::DEFAULT;

    public const BASE_MODAL = VariableHelper::MODAL;

    public const BASE_PANEL = 'panel';

    public const BASE_OVERLAY = 'overlay';

    public const BASE_PAGE = VariableHelper::PAGE;

    public const OUTPUT_TYPE_RESPONSE_HTML = VariableHelper::HTML;
    public const OUTPUT_TYPE_RESPONSE_JSON = VariableHelper::JSON;

    public const OUTPUT_TYPES = [
        self::OUTPUT_TYPE_RESPONSE_HTML,
        self::OUTPUT_TYPE_RESPONSE_JSON,
    ];

    public InitialLayoutRenderNode|AjaxLayoutRenderNode $layoutRenderNode;

    protected array $contextRenderNodeRegistry = [];

    protected array $contextRenderNodeStack = [];

    public array $usagesConfig = [];

    public ?bool $enableAggregation = null;

    private bool $debug = false;

    private string $outputType = self::OUTPUT_TYPE_RESPONSE_HTML;

    protected string $layoutBase = self::BASE_DEFAULT;

    /**
     * @var array<string|null>
     */
    public array $usages = [];

    public array $registry = [
        \Wexample\WebRenderNode\Helper\RenderingHelper::CONTEXT_COMPONENT => [],
        \Wexample\WebRenderNode\Helper\RenderingHelper::CONTEXT_PAGE => [],
        \Wexample\WebRenderNode\Helper\RenderingHelper::CONTEXT_LAYOUT => [],
        \Wexample\WebRenderNode\Helper\RenderingHelper::CONTEXT_VUE => [],
    ];

    private bool $useJs = true;

    public function __construct(
        string $view,
        protected AssetsRegistry $assetsRegistry
    )
    {
        $this->setView($view);
        $this->createRenderRequestId();
    }

    public function registerRenderNode(
        AbstractRenderNode $renderNode
    ): void {
        $contextType = $renderNode->getContextType();

        if (!isset($this->registry[$contextType])) {
            $this->registry[$contextType] = [];
        }

        $this->registry[$contextType][$renderNode->getView()] = $renderNode;
    }

    public function createRenderRequestId(): string
    {
        $this->setRenderRequestId(uniqid());

        return $this->getRenderRequestId();
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
            $renderNode->getView()
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
        $usagesTypes = $this->usagesConfig[ResponsiveAssetUsageService::getName()]['list'] ?? [];
        $breakpoints = [];

        foreach ($usagesTypes as $name => $config) {
            $breakpoints[$name] = $config['breakpoint'] ?? null;
        }

        return $breakpoints;
    }

    public function getUsage(
        string $usageName,
    ): ?string
    {
        return $this->usages[$usageName];
    }

    public function setUsage(
        string $usageName,
        ?string $usageValue
    ): void
    {
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

    public function getLayoutRenderNode(): AbstractLayoutRenderNode
    {
        return $this->layoutRenderNode;
    }

    public function setLayoutRenderNode(AbstractLayoutRenderNode $layoutRenderNode): void
    {
        $this->layoutRenderNode = $layoutRenderNode;
    }

    public function getAssetsRegistry(): AssetsRegistry
    {
        return $this->assetsRegistry;
    }
}
