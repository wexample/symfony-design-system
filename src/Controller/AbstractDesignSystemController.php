<?php

namespace Wexample\SymfonyDesignSystem\Controller;


use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Wexample\SymfonyDesignSystem\Helper\DesignSystemHelper;
use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\InitialLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyHelpers\Class\AbstractBundle;
use Wexample\SymfonyHelpers\Controller\AbstractController;
use Wexample\SymfonyTemplate\Helper\TemplateHelper;

abstract class AbstractDesignSystemController extends AbstractController
{
    public function __construct(
        protected readonly AdaptiveResponseService $adaptiveResponseService,
    )
    {
    }

    protected function createRenderPass(string $view): RenderPass
    {
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

        $renderPass->setLayoutBase(
            $this->adaptiveResponseService->detectLayoutBase($renderPass)
        );

        return $this->configureRenderPass($renderPass);
    }

    protected function configureRenderPass(
        RenderPass $renderPass
    ): RenderPass
    {
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
    ): Response
    {
        $renderPass = $renderPass ?: $this->createRenderPass($view);

        $renderPass->setLayoutRenderNode(new InitialLayoutRenderNode());

        return $this->renderRenderPass(
            $renderPass,
            $parameters + [
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
    ): Response
    {
        $view = $renderPass->getView();

        $response = $this->render(
            $view,
            [
                'render_pass' => $renderPass,
            ] + $parameters,
            $response
        );

        return $this->injectLayoutAssets(
            $response,
            $renderPass
        );
    }

    protected function injectLayoutAssets(
        Response $response,
        RenderPass $renderPass
    ): Response
    {
        if ($response instanceof JsonResponse
            || $response->isClientError()
            || $response->isServerError()
        ) {
            return $response;
        }

        $assetsIncludes = $this->renderView(
            '@WexampleSymfonyDesignSystemBundle/macros/assets.html.twig',
            [
                'render_pass' => $renderPass,
            ]
        );

        $content = $response->getContent();

        if ($content && str_contains($content, RenderingHelper::PLACEHOLDER_PRELOAD_TAG)) {
            $content = str_replace(
                RenderingHelper::PLACEHOLDER_PRELOAD_TAG,
                $assetsIncludes,
                $content
            );
        } else {
            $content .= $assetsIncludes;
        }

        if ($this->getParameter('design_system.debug') ?? false) {
            $content .= $this->renderView(
                '@WexampleSymfonyDesignSystemBundle/macros/debug.html.twig',
                [
                    'render_pass' => $renderPass,
                ]
            );
        }

        $response->setContent($content);

        return $response;
    }

    /**
     * @return string Allow bundle-specific front template directories.
     */
    public static function getTemplateLocationPrefix(
        AbstractBundle|string $bundle = null
    ): string
    {
        return ($bundle ? $bundle::getAlias() : DesignSystemHelper::TWIG_NAMESPACE_FRONT);
    }

    public static function getTemplateFrontDir(
        AbstractBundle|string $bundle = null
    ): string
    {
        return ($bundle ? DesignSystemHelper::TWIG_NAMESPACE_ASSETS : DesignSystemHelper::TWIG_NAMESPACE_FRONT);
    }

    /**
     * Based on the controller name, find the matching template dir.
     * The controller and its templates should follow the same directories structure.
     * ex:
     *   - Config/DesignSystem/AppController.php
     *   - config/design_system/app/(index.html.twig)
     */
    public static function getControllerTemplateDir(
        string $bundle = null
    ): string
    {
        return TemplateHelper::joinNormalizedParts(
            TemplateHelper::explodeControllerNamespaceSubParts(
                controllerName: static::class,
                bundleClassPath: $bundle
            )
        );
    }
}
