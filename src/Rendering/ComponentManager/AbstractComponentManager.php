<?php

namespace Wexample\SymfonyDesignSystem\Rendering\ComponentManager;

use Symfony\Component\HttpKernel\KernelInterface;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\ComponentRenderNode;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;

abstract class AbstractComponentManager
{
    public function __construct(
        protected KernelInterface $kernel,
        protected AdaptiveResponseService $adaptiveResponseService,
    ) {
    }

    public function createComponent(ComponentRenderNode $componentRenderNode)
    {
        // To override...
    }

    public function postRender()
    {
        // To override...
    }
}
