<?php

namespace Wexample\SymfonyDesignSystem\Rendering\Traits;

use Wexample\SymfonyDesignSystem\Helper\DomHelper;

trait WithDomIdFromTemplateAbstractPathTrait
{
    use WithTemplateAbstractPathTrait;

    private string $domId;

    private string $domPrefix;

    public function getDomId(): string
    {
        return $this->getDomPrefix().'-'.DomHelper::buildStringIdentifier($this->getTemplateAbstractPath());
    }

    abstract public function getDomPrefix(): string;

    public function setDomPrefix(string $domPrefix): void
    {
        $this->domPrefix = $domPrefix;
    }
}