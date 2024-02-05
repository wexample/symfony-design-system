<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Wexample\SymfonyDesignSystem\Controller\AbstractController;
use Wexample\SymfonyDesignSystem\Rendering\AdaptiveResponse;

class AdaptiveResponseService
{
    private ?AdaptiveResponse $currentResponse = null;

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function renderPrepare(
        AbstractController $controller,
    ): void {
        // Response may be explicitly created in controller,
        // but if not we need at least one to detect layout base name.
        if (!$this->hasResponse()) {
            $this->createResponse($controller);
        }
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
