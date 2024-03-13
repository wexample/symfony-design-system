<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Exception;
use Wexample\SymfonyDesignSystem\Rendering\Traits\WithDomId;
use Wexample\SymfonyDesignSystem\Rendering\Traits\WithTemplateAbstractPathTrait;

class Vue
{
    use WithTemplateAbstractPathTrait;
    use WithDomId;

    /**
     * @throws Exception
     */
    public function __construct(string $templateAbstractPath)
    {
        $this->setTemplateAbstractPath($templateAbstractPath);
    }
}
