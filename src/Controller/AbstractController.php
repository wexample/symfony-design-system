<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Wexample\SymfonyDesignSystem\Helper\TemplateHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AjaxLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\InitialLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyDesignSystem\Service\LayoutService;
use Wexample\SymfonyDesignSystem\Service\RenderPassBagService;
use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;
use Wexample\SymfonyHelpers\Helper\BundleHelper;

abstract class AbstractController extends \Wexample\SymfonyHelpers\Controller\AbstractController
{
    public function __construct(
        readonly protected AdaptiveResponseService $adaptiveResponseService,
        readonly protected LayoutService $layoutService,
        readonly protected RenderPassBagService $renderPassBagService,
    ) {
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

        $renderPass->setLayoutBase(
            $this->adaptiveResponseService->detectLayoutBase($renderPass)
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
     * @throws Exception
     */
    protected function adaptiveRender(
        string $view,
        array $parameters = [],
        Response $response = null,
        RenderPass $renderPass = null
    ): Response {
        $renderPass = $renderPass ?: $this->createRenderPass($view);

        // Store it for post render events.
        $this->renderPassBagService->setRenderPass($renderPass);

        $className = RenderPass::OUTPUT_TYPE_RESPONSE_JSON === $renderPass->getOutputType()
            ? AjaxLayoutRenderNode::class
            : InitialLayoutRenderNode::class;

        $renderPass->layoutRenderNode = new $className;
        $renderPass->setView($view);

        if ($renderPass->isJsonRequest()) {
            $renderPass->layoutRenderNode = new AjaxLayoutRenderNode();

            $this->layoutService->initRenderNode(
                $renderPass->layoutRenderNode,
                $renderPass,
                $view
            );

            try {
                $renderPasseResponse = $this->renderRenderPass(
                    $renderPass,
                    $parameters,
                    $response,
                );

                $renderPass->layoutRenderNode->setBody(
                    trim($renderPasseResponse->getContent())
                );

                $finalResponse = new JsonResponse(
                    $renderPass->layoutRenderNode->toRenderData());

                $finalResponse->setStatusCode(
                    $renderPasseResponse->getStatusCode()
                );


                // Prevents browser to display json response when
                // clicking on back button.
                $finalResponse->headers->set('Vary', 'Accept');

                return $finalResponse;
            } catch (\Exception $exception) {
                $errorView = BundleHelper::ALIAS_PREFIX.
                    WexampleSymfonyDesignSystemBundle::getAlias()
                    .'/'.AbstractPagesController::RESOURCES_DIR_PAGE
                    .'system/error'
                    .TemplateHelper::TEMPLATE_FILE_EXTENSION;

                if ($view !== $errorView) {
                    $errorResponse = new JsonResponse();
                    $errorResponse->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);

                    return $this->adaptiveRender(
                        $errorView,
                        [
                            'exception' => $exception,
                        ],
                        $errorResponse
                    );
                }

                return new JsonResponse($exception->getMessage());
            }
        }

        return $this->renderRenderPass(
            $renderPass,
            $parameters + [
                'display_breakpoints' => $renderPass->getDisplayBreakpoints(),
            ],
            $response,
        );
    }

    /**
     * @throws Exception
     */
    public function renderRenderPass(
        RenderPass $renderPass,
        array $parameters = [],
        Response $response = null,
    ): Response {
        $view = $renderPass->getView();

        if (!$view) {
            throw new Exception('View must be defined before adaptive rendering');
        }

        return $this->render(
            $view,
            [
                'debug' => (bool) $this->getParameter('design_system.debug'),
                'render_pass' => $renderPass,
            ] + $parameters,
            $response
        );
    }
}
