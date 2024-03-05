<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Helper\DomHelper;
use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderDataGenerator;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AssetsService;

abstract class AbstractRenderNode extends RenderDataGenerator
{
    public array $assets = AssetsService::ASSETS_DEFAULT_EMPTY;

    public array $components = [];

    public string $cssClassName;

    protected string $id;

    public bool $hasAssets = true;

    public array $translations = [];

    public array $vars = [];

    private string $name;

    public array $usages;

    abstract public function getContextType(): string;

    public function init(
        RenderPass $renderPass,
        string $name,
    ): void {
        $this->setName($name);
        $this->id = $this->getContextType().'-'
            .str_replace('/', '-', $this->getName())
            .'-'.uniqid();
        $this->usages = $renderPass->usages;

        $this->cssClassName = DomHelper::buildCssClassName($this->id);

        $renderPass->registerContextRenderNode($this);

        $renderPass->registerRenderNode($this);
    }

    public function getContextRenderNodeKey(): string
    {
        return RenderingHelper::buildRenderContextKey(
            $this->getContextType(),
            $this->getName()
        );
    }

    public function toRenderData(): array
    {
        return [
            'assets' => [
                Asset::EXTENSION_CSS => $this->arrayToRenderData($this->assets[Asset::EXTENSION_CSS]),
                Asset::EXTENSION_JS => $this->arrayToRenderData($this->assets[Asset::EXTENSION_JS]),
            ],
            'components' => $this->arrayToRenderData($this->components),
            'cssClassName' => $this->cssClassName,
            'id' => $this->id,
            'name' => $this->getName(),
            'translations' => $this->translations,
            'vars' => $this->vars,
            'usages' => $this->usages,
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
