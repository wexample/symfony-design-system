<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderDataGenerator;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AssetsService;

abstract class AbstractRenderNode extends RenderDataGenerator
{
    public array $assets = AssetsService::ASSETS_DEFAULT_EMPTY;

    public array $components = [];

    protected string $id;

    public ?AbstractRenderNode $parent = null;

    public bool $hasAssets = true;

    public string $renderRequestId;

    public array $translations = [];

    public array $vars = [];

    public string $name;

    abstract public function getContextType(): string;

    public function __construct(
        protected RenderPass $renderPass
    ) {
    }

    public function init(
        string $name,
    ) {
        $this->parent = $this->renderPass->getCurrentContextRenderNode();
        $this->renderRequestId = $this->renderPass->getRenderRequestId();
        $this->name = $name;
        $this->id = $this->getContextType().'-'
            .str_replace('/', '-', $this->name)
            .'-'.uniqid();

        $this->renderPass->registerContextRenderNode($this);

        $this->renderPass->registerRenderNode($this);
    }

    public function getContextRenderNodeKey(): string
    {
        return RenderingHelper::buildRenderContextKey(
            $this->getContextType(),
            $this->getRenderContextName()
        );
    }

    protected function getRenderContextName(): string
    {
        return $this->name;
    }

    public function getComponentsTemplates(): string
    {
        $output = '';

        /** @var ComponentRenderNode $component */
        foreach ($this->components as $component) {
            $output .= $component->body;
        }

        return $output;
    }

    public function toRenderData(): array
    {
        return [
            'assets' => [
                'css' => $this->arrayToRenderData($this->assets['css']),
                'js' => $this->arrayToRenderData($this->assets['js']),
            ],
            'components' => $this->arrayToRenderData($this->components),
            'id' => $this->id,
            'name' => $this->name,
            'renderRequestId' => $this->renderRequestId,
            'translations' => $this->translations,
            'vars' => $this->vars,
        ];
    }
}
