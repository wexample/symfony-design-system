<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Wexample\SymfonyDesignSystem\Controller\AbstractController;
use Wexample\SymfonyDesignSystem\Rendering\AdaptiveResponse;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;

class AdaptiveResponseService
{
    public const EVENT_METHODS_PREFIX = 'renderEvent';

    public const EVENT_NAME_POST_RENDER = 'PostRender';

    private ?AbstractController $controller = null;

    private ?AdaptiveResponse $currentResponse = null;

    private array $renderEventListeners = [];

    public RenderPass $renderPass;

    public bool $enableAggregation;

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

    public function addRenderEventListener(object $service)
    {
        $this->renderEventListeners[] = $service;
    }

    public function triggerRenderEvent(string $eventName, array &$options = []): array
    {
        $eventName = AdaptiveResponseService::EVENT_METHODS_PREFIX.ucfirst($eventName);

        foreach ($this->renderEventListeners as $service)
        {
            if (method_exists($service, $eventName))
            {
                $service->$eventName($options);
            }
        }

        return $options;
    }
}
