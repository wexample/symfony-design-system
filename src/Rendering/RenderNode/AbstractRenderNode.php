<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Rendering\RenderDataGenerator;

abstract class AbstractRenderNode extends RenderDataGenerator
{
    public string $name;

    abstract public function getContextType(): string;

    public function init(
        string $name,
    ): void {
        $this->name = $name;
    }
}
