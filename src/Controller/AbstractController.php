<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Controller\Traits\AdaptiveResponseControllerTrait;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;

abstract class AbstractController extends \Wexample\SymfonyHelpers\Controller\AbstractController
{
    /* Set methods for adaptive rendering. */
    use AdaptiveResponseControllerTrait;

    public const ROUTE_OPTION_KEY_EXPOSE = 'expose';

    public const ROUTE_OPTIONS_ONLY_EXPOSE = [self::ROUTE_OPTION_KEY_EXPOSE => true];

    public ?bool $enableAggregation = null;

    public bool $enableJavascript = true;

    public string $requestUri;

    public bool $templateUseJs;

    public function __construct(
        protected AdaptiveResponseService $adaptiveResponseService,
        protected AssetsService $assetsService,
        protected Environment $twigEnvironment
    ) {
    }

    /**
     * As adaptive response plays with controller rendering,
     * we should create a way to execute render from outside
     * using this public method.
     */
    public function adaptiveRender(
        string $view,
        array $parameters = [],
        Response $response = null
    ): ?Response {
        return $this->render(
            $view,
            $parameters,
            $response
        );
    }

    /**
     * Overrides default render, adding some magic.
     */
    protected function render(
        string $view,
        array $parameters = [],
        Response $response = null
    ): Response {
        $pass = $this->createRenderPass($view);

        return parent::render(
            $view,
            $parameters,
            $response
        );
    }

    public function getTwigEnvironment(): Environment
    {
        return $this->twigEnvironment;
    }

    protected function renderView(
        string $view,
        array $parameters = []
    ): string {
        try {
            $rendered = parent::renderView(
                $view,
                $parameters
            );
        } catch (Exception $exception) {
            $rendered = parent::renderView(
                WexampleSymfonyDesignSystemBundle::getTemplatePath('pages/_core/error/default.html.twig'),
                $parameters + [
                    'message' => $exception->getMessage(),
                ]
            );
        }

        $options = [
            'view' => $view,
            'rendered' => $rendered,
        ];

        $this->renderEventListenerService->triggerRenderEvent(
            RenderEventListenerService::EVENT_NAME_POST_RENDER,
            $options
        );

        return $options['rendered'];
    }
}
