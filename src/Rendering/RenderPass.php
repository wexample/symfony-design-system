<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Wexample\SymfonyDesignSystem\Rendering\Traits\WithView;

class RenderPass
{
    use WithView;

    public function __construct(
        string $view
    )
    {
        $this->setView($view);
    }
}
