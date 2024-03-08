<?php

namespace Wexample\SymfonyDesignSystem\Rendering\Traits;

trait WithTemplateNameTrait
{
    private string $templateName;

    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    public function setTemplateName(string $templateName): void
    {
        $this->templateName = $templateName;
    }
}