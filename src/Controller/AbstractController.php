<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AjaxLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\InitialLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyDesignSystem\Service\RenderPassBagService;

abstract class AbstractController extends \Wexample\SymfonyHelpers\Controller\AbstractController
{
    public function __construct(
        readonly protected AdaptiveResponseService $adaptiveResponseService,
        readonly protected RenderPassBagService $renderPassBagService,
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
        Response $response = null,
        RenderPass $renderPass = null
    ): ?Response {
        return $this->render(
            $view,
            $parameters,
            $response,
            renderPass: $renderPass
        );
    }

    protected function createPageRenderPass(
        string $pageName
    ): RenderPass {
        return $this->createRenderPass(
            $this->buildTemplatePath($pageName),
        );
    }

    protected function createRenderPass(
        string $view
    ): RenderPass {
        $renderPass = new RenderPass($view);

        /** @var ParameterBagInterface $parameterBag */
        $parameterBag = $this->container->get('parameter_bag');
        foreach (AssetsService::getAssetsUsagesStatic() as $usageStatic) {
            $usageName = $usageStatic::getName();
            $key = 'design_system.usages.'.$usageName;

            $config = $parameterBag->has($key) ? $this->getParameter($key) : ['list' => []];
            $renderPass->usagesConfig[$usageName] = $config;

            $renderPass->setUsage(
                $usageName,
                $config['default'] ?? null
            );
        }

        $renderPass->enableAggregation = $this->getParameterOrDefault(
            'design_system.enable_aggregation',
            false
        );

        $renderPass->setDebug(
            $this->getParameterOrDefault(
                'design_system.debug',
                false
            )
        );

        $renderPass->setOutputType(
            $this->adaptiveResponseService->detectOutputType()
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
        Response $response = null,
        RenderPass $renderPass = null
    ): Response {
        $renderPass = $renderPass ?: $this->createRenderPass($view);

        // Store it for post render events.
        $this->renderPassBagService->setRenderPass($renderPass);

        if ($renderPass->isJsonRequest()) {
            $renderPass->layoutRenderNode = new AjaxLayoutRenderNode();

            return new JsonResponse((object) [
                'assets' => [],
            ]);
        }

        $renderPass->layoutRenderNode = new InitialLayoutRenderNode();

        return parent::render(
            $view,
            [
                'debug' => (bool) $this->getParameter('design_system.debug'),
                'display_breakpoints' => $renderPass->getDisplayBreakpoints(),
                'render_pass' => $renderPass,
            ] + $parameters + $renderPass->getRenderParameters(),
            $response
        );
    }
}
