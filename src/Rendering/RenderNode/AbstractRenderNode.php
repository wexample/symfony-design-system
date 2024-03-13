<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Helper\DomHelper;
use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderDataGenerator;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Rendering\Traits\WithTemplateAbstractPathTrait;
use Wexample\SymfonyDesignSystem\Rendering\Traits\WithView;
use Wexample\SymfonyDesignSystem\Service\AssetsService;

abstract class AbstractRenderNode extends RenderDataGenerator
{
    use WithTemplateAbstractPathTrait;
    use WithView;

    public array $assets = AssetsService::ASSETS_DEFAULT_EMPTY;

    public array $components = [];

    public string $cssClassName;

    protected string $id;

    public bool $hasAssets = true;

    public array $translations = [];

    public array $vars = [];

    private string $templateAbstractPath;

    public array $usages;

    private array $inheritanceStack = [];

    abstract public function getContextType(): string;

    public function init(
        RenderPass $renderPass,
        string $templateName,
        string $name,
    ): void {
        $this->setDefaultTemplateName($templateName);
        $this->setTemplateAbstractPath($name);

        $this->id = implode('-', [
            $this->getContextType(),
            str_replace('/', '-', $this->getTemplateAbstractPath()),
            uniqid(),
        ]);

        $this->usages = $renderPass->usages;
        $this->cssClassName = DomHelper::buildStringIdentifier($this->id);

        $renderPass->registerContextRenderNode($this);
        $renderPass->registerRenderNode($this);
    }

    public function getContextRenderNodeKey(): string
    {
        return RenderingHelper::buildRenderContextKey(
            $this->getContextType(),
            $this->getTemplateAbstractPath()
        );
    }

    public function getComponentsTemplates(): ?string
    {
        $output = [];

        /** @var ComponentRenderNode $component */
        foreach ($this->components as $component) {
            if ($body = $component->getBody()) {
                $output[] = $component->getBody();
            }
        }

        return !empty($output) ? implode($output) : null;
    }

    public function toRenderData(): array
    {
        $data = [
            'components' => $this->arrayToRenderData($this->components),
            'cssClassName' => $this->cssClassName,
            'id' => $this->id,
            'templateAbstractPath' => $this->getTemplateAbstractPath(),
            'translations' => (object) $this->translations,
            'vars' => (object) $this->vars,
            'usages' => (object) $this->usages,
        ];

        if ($this->hasAssets) {
            $data['assets'] = [
                Asset::EXTENSION_CSS => $this->arrayToRenderData($this->assets[Asset::EXTENSION_CSS]),
                Asset::EXTENSION_JS => $this->arrayToRenderData($this->assets[Asset::EXTENSION_JS]),
            ];
        }

        return $data;
    }

    public function setDefaultTemplateName(string $templateName): void
    {
        if (!$this->getView()) {
            $this->setView($templateName);
        }

        $this->inheritanceStack[] = $templateName;
    }

    public function getInheritanceStack(): array
    {
        return $this->inheritanceStack;
    }
}
