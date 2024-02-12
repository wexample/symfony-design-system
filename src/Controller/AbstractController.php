<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Symfony\Component\HttpFoundation\Response;
use Wexample\SymfonyDesignSystem\Helper\ColorSchemeHelper;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;

abstract class AbstractController extends \Wexample\SymfonyHelpers\Controller\AbstractController
{
    public function __construct(
        protected AdaptiveResponseService $adaptiveResponseService,
    ) {

    }

    public function getDisplayBreakpoints(): array
    {
        return $this->getParameter('design_system.display_breakpoints');
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
        $pass = $this
            ->adaptiveResponseService
            ->createRenderPass(
                $this,
                $view
            );

        return parent::render(
            $view,
            [
                'debug' => (bool) $this->getParameter('design_system.debug'),
                'display_breakpoints' => $this->getParameter('design_system.display_breakpoints'),
                'layout_color_scheme' => ColorSchemeHelper::SCHEME_DEFAULT,
                'render_pass' => $pass,
            ] + $parameters + $pass->getRenderParameters(),
            $response
        );
    }
}
