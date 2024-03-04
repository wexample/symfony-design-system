<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Wexample\SymfonyDesignSystem\Service\AssetsService;

abstract class AbstractController extends \Wexample\SymfonyHelpers\Controller\AbstractController
{
    public function __construct(
        protected AdaptiveResponseService $adaptiveResponseService,
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

        /** @var ParameterBagInterface $parameterBag */
        $parameterBag = $this->container->get('parameter_bag');
        foreach (AssetsService::getAssetsUsagesStatic() as $usageStatic) {
            $usageName = $usageStatic::getName();
            $key = 'design_system.usages.'.$usageName;

            $config = $parameterBag->has($key) ? $this->getParameter($key) : ['list' => []];
            $renderPass->usagesList[$usageName] = $config;

            $renderPass->setUsage(
                $usageName,
                $config['default'] ?? null
            );
        }

        $renderPass->enableAggregation = $this->getParameterOrDefault(
            'design_system.enable_aggregation',
            false
        );

        return $this->configureRenderPass($renderPass);
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
                'display_breakpoints' => $pass->getDisplayBreakpoints(),
                'render_pass' => $pass,
            ] + $parameters + $pass->getRenderParameters(),
            $response
        );
    }
}
