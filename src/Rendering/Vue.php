<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Exception;
use Wexample\SymfonyDesignSystem\Rendering\Traits\WithDomIdFromTemplateAbstractPathTrait;

class Vue
{
    use WithDomIdFromTemplateAbstractPathTrait;

    /**
     * @throws Exception
     */
    public function __construct(string $templateAbstractPath)
    {
        $this->setTemplateAbstractPath($templateAbstractPath);
    }

    public function getDomPrefix(): string
    {
        return 'vue';
    }
}
