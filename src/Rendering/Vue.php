<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Exception;
use Wexample\SymfonyDesignSystem\Helper\DomHelper;
use Wexample\SymfonyDesignSystem\Rendering\Traits\WithTemplateNameTrait;

class Vue
{
    use WithTemplateNameTrait;

    public string $domId;

    /**
     * @throws Exception
     */
    public function __construct(string $templateName)
    {
        $this->setTemplateName($templateName);

        $this->domId = DomHelper::buildStringIdentifier(
            $this->getTemplateName()
        );
    }
}
