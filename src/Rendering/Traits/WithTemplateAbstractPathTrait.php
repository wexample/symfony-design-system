<?php

namespace Wexample\SymfonyDesignSystem\Rendering\Traits;

trait WithTemplateAbstractPathTrait
{
    private string $templateAbstractPath;

    public function getTemplateAbstractPath(): string
    {
        return $this->templateAbstractPath;
    }

    public function setTemplateAbstractPath(string $templateAbstractPath): void
    {
        $this->templateAbstractPath = $templateAbstractPath;
    }
}