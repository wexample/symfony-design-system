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
        return parent::render(
            $view,
            $parameters,
            $response
        );
    }
}
