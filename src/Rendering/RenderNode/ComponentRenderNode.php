<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Wexample\SymfonyDesignSystem\Helper\DomHelper;
use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Helper\TemplateHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

class ComponentRenderNode extends AbstractRenderNode
{
    private ?string $body;

    public function __construct(
        public string $initMode,
        public array $options = []
    ) {

    }

    public function init(
        RenderPass $renderPass,
        string $templateName,
        string $name,
    ): void {
        parent::init($renderPass, $templateName, $name);

        $renderPass
            ->getCurrentContextRenderNode()
            ->components[] = $this;
    }

    public function getContextType(): string
    {
        return RenderingHelper::CONTEXT_COMPONENT;
    }

    public function renderCssClasses(): string
    {
        return 'com-class-loaded '.$this->cssClassName;
    }

    public function renderTag(): string
    {
        return DomHelper::buildTag(
            'span',
            [
                // ID are not used as "id" html attribute,
                // as component may be embedded into a vue,
                // so replicated multiple times.
                VariableHelper::CLASS_VAR => 'com-init '.$this->cssClassName,
            ]
        );
    }

    public function toRenderData(): array
    {
        return parent::toRenderData()
            + [
                'initMode' => $this->initMode,
                'options' => $this->options,
            ];
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function render(Environment $env): void
    {
        $this->setBody($env->render(
            $this->getTemplatePath(),
            $this->options
        ));
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): void
    {
        $this->body = $body;
    }

    public function getTemplatePath(): string
    {
        return $this->getView().TemplateHelper::TEMPLATE_FILE_EXTENSION;
    }
}
