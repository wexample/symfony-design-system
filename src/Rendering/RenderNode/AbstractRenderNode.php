<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Helper\DomHelper;
use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderDataGenerator;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Rendering\Traits\WithTemplateAbstractPathTrait;
use Wexample\SymfonyDesignSystem\Rendering\Traits\WithTemplateNameTrait;
use Wexample\SymfonyDesignSystem\Service\AssetsService;

abstract class AbstractRenderNode extends RenderDataGenerator
{
    use WithTemplateAbstractPathTrait;
    use WithTemplateNameTrait;

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
        $this->setTemplateName($templateName);
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
            'templateAbstractPath' => $this->getTemplateAbstractPath(),
            'translations' => $this->translations,
            'vars' => $this->vars,
            'usages' => $this->usages,
        ];
    }

    public function setDefaultTemplateName(string $templateName): void
    {
        if (!$this->getTemplateName()) {
            $this->setTemplateName($templateName);
        }

        $this->inheritanceStack[] = $templateName;
    }

    public function getInheritanceStack(): array
    {
        return $this->inheritanceStack;
    }
}
