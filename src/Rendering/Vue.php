<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Exception;
use Wexample\SymfonyDesignSystem\Helper\DomHelper;
use Wexample\SymfonyDesignSystem\Rendering\Traits\WithTemplateAbstractPathTrait;

class Vue
{
    use WithTemplateAbstractPathTrait;

    public string $domId;

    /**
     * @throws Exception
     */
    public function __construct(string $templateAbstractPath)
    {
        $this->setTemplateAbstractPath($templateAbstractPath);

        $this->domId = DomHelper::buildStringIdentifier(
            $this->getTemplateAbstractPath()
        );
    }
}
