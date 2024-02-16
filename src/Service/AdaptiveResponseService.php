<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Wexample\SymfonyDesignSystem\Controller\AbstractController;
use Wexample\SymfonyDesignSystem\Rendering\AdaptiveResponse;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;

class AdaptiveResponseService
{
    private ?AdaptiveResponse $currentResponse = null;

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function createRenderPass(
        AbstractController $controller,
        string $view,
    ): RenderPass {
        $renderPass = new RenderPass(
        // Response may be explicitly created in controller,
        // but if not we need at least one to detect layout base name.
            ($this->hasResponse() ? $this->getResponse() : $this->createResponse($controller))->getOutputType(),
            $view,
        );

        return $renderPass;
    }

    public function hasResponse(): bool
    {
        return $this->currentResponse !== null;
    }

    public function createResponse(AbstractController $controller): AdaptiveResponse
    {
        $this->currentResponse = new AdaptiveResponse(
            $this->requestStack->getMainRequest(),
            $controller,
            $this
        );

        return $this->getResponse();
    }


    public function getResponse(): AdaptiveResponse
    {
        return $this->currentResponse;
    }
}
