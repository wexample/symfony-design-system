<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Wexample\WebRenderNode\Rendering\RenderNode\AbstractLayoutRenderNode;
use Wexample\WebRenderNode\Rendering\Traits\WithView;

use Wexample\SymfonyDesignSystem\Service\Usage\ResponsiveAssetUsageService;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

class RenderPass
{
    use WithView;

    public const BASE_DEFAULT = VariableHelper::DEFAULT;

    public const OUTPUT_TYPE_RESPONSE_HTML = VariableHelper::HTML;

    private AbstractLayoutRenderNode $layoutRenderNode;

    public array $usagesConfig = [];


    public ?bool $enableAggregation = null;
    private string $outputType = self::OUTPUT_TYPE_RESPONSE_HTML;

    protected string $layoutBase = self::BASE_DEFAULT;

    /**
     * @var array<string|null>
     */
    public array $usages = [];



    private bool $useJs = true;
    public function __construct(
        string $view,
        protected AssetsRegistry $assetsRegistry
    )
    {
        $this->setView($view);
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
        if (! isset($this->usagesConfig[$usageName])) {
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

    public function getOutputType(): string
    {
        return $this->outputType;
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
