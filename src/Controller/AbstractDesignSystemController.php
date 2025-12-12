<?php

namespace Wexample\SymfonyDesignSystem\Controller;


use Exception;
use Symfony\Component\HttpFoundation\Response;
use Wexample\SymfonyDesignSystem\Helper\DesignSystemHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\InitialLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
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

        $env = $this->getParameter('design_system.environment');

        $renderPass->layoutRenderNode = new InitialLayoutRenderNode($env);

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

        return $this->render(
            $view,
            [
                'render_pass' => $renderPass,
            ] + $parameters,
            $response
        );
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
