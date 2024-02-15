<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Symfony\Component\HttpFoundation\Response;
use Wexample\SymfonyDesignSystem\Helper\ColorSchemeHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
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

    protected function createRenderPass(
        string $view
    ): RenderPass {
        $responseService = $this->adaptiveResponseService;

        $renderPass = new RenderPass(
        // Response may be explicitly created in controller,
        // but if not we need at least one to detect layout base name.
            ($responseService->hasResponse() ? $responseService->getResponse() : $responseService->createResponse($this))->getOutputType(),
            $view,
        );

        $renderPass->displayBreakpoints = $this->getDisplayBreakpoints();
        $renderPass->colorSchemes = $this->getParameter('design_system.color_schemes');
        $renderPass->colorScheme = $this->getParameter('design_system.color_scheme_default') ?: array_key_first($renderPass->colorSchemes);

        return $this->configureRenderPAss($renderPass);
    }

    protected function configureRenderPass(
        RenderPass $renderPass
    ): RenderPass {

        return $renderPass;
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
            [
                'debug' => (bool) $this->getParameter('design_system.debug'),
                'display_breakpoints' => $pass->displayBreakpoints,
                'layout_color_scheme' => ColorSchemeHelper::SCHEME_DEFAULT,
                'render_pass' => $pass,
            ] + $parameters + $pass->getRenderParameters(),
            $response
        );
    }
}
