<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode\Traits;


use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyTemplate\Helper\TemplateHelper;

trait DesignSystemRenderNodeTrait {
    private array $inheritanceStack = [];

    public function init(
        RenderPass $renderPass,
        string $view
    ): void
    {
        $this->setDefaultView($view);

        $this->id = implode('-', [
            $this->getContextType(),
            str_replace('/', '-', $this->getView()),
            uniqid(),
        ]);
    }

    public function setDefaultView(string $view): void
    {
        $view = TemplateHelper::removeExtension($view);

        if (! $this->getView()) {
            $this->setView($view);
        }

        $this->inheritanceStack[] = $view;
    }
}