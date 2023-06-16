<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Wexample\SymfonyDesignSystem\Controller\AbstractController;
use Wexample\SymfonyDesignSystem\Rendering\AdaptiveResponse;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;

class AdaptiveResponseService
{
    private ?AbstractController $controller = null;
    private ?AdaptiveResponse $currentResponse = null;

    public RenderPass $renderPass;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly KernelInterface $kernel,
    ) {
    }

    public function renderPrepare(
        string $view,
        array &$parameters = []
    ): void {
        // Response may be explicitly created in controller,
        // but if not we need at least one to detect layout base name.
        if (!$this->getResponse())
        {
            $this->createResponse($this->controller);
        }

        $this->renderPass = new RenderPass(
            $this->getResponse(),
            $this->controller->enableAggregation,
            $this->requestStack->getMainRequest(),
            $this->controller->enableJavascript,
            $view,
        );

        $this->renderPass->prepare(
            $parameters,
            $this->kernel->getEnvironment()
        );
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

    public function getResponse(): ?AdaptiveResponse
    {
        return $this->currentResponse;
    }

    public function setController(
        AbstractController $controller
    ): self {
        $this->controller = $controller;

        return $this;
    }

    public function getController(): ?AbstractController
    {
        return $this->controller;
    }
}
