<?php

namespace Wexample\SymfonyDesignSystem\Controller;
use Symfony\Component\HttpFoundation\Response;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;

abstract class AbstractController extends \Wexample\SymfonyHelpers\Controller\AbstractController
{
    public ?bool $enableAggregation = null;
    public bool $enableJavascript = true;

    public function __construct(
        protected AdaptiveResponseService $adaptiveResponseService,
    ) {

    }

    /**
     * Overrides default render, adding some magic.
     *
     * @param Response|null $response
     */
    protected function render(
        string $view,
        array $parameters = [],
        Response $response = null
    ): Response {
        $this->adaptiveResponseService->setController($this);

        // Allow controller to enable or not properties.
        if (is_null($this->enableAggregation))
        {
            $this->enableAggregation = $this->getParameter('design_system.enable_aggregation');
        }

        $this->adaptiveResponseService->renderPrepare(
            $view,
            $parameters
        );

        return parent::render(
            $view,
            $parameters,
            $response
        );
    }
}
