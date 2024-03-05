<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Wexample\SymfonyDesignSystem\Service\JsService;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

class AdaptiveResponseExtension extends AbstractExtension
{
    /**
     * CommonExtension constructor.
     */
    public function __construct(
        protected AdaptiveResponseService $adaptiveResponseService,
        protected RequestStack $requestStack,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'adaptive_response_revert_context',
                [
                    $this,
                    'adaptiveResponseRevertContext',
                ]
            ),
            new TwigFunction(
                'adaptive_response_set_page_context',
                [
                    $this,
                    'adaptiveResponseSetPageContext',
                ]
            ),
            new TwigFunction(
                'adaptive_response_rendering_base_path',
                [
                    $this,
                    'adaptiveResponseRenderingBasePath',
                ],
                [
                    self::FUNCTION_OPTION_NEEDS_CONTEXT => true,
                ]
            ),
            new TwigFunction(
                'adaptive_rendering_base',
                [
                    $this,
                    'adaptiveRenderingBase',
                ]
            ),
        ];
    }

    /**
     * Return base layout path regarding request type
     * and template configuration.
     */
    public function adaptiveResponseRenderingBasePath(array $context): string
    {
        return $this
            ->adaptiveResponseService
            ->getResponse()
            ->getRenderingBasePath($context);
    }

    public function adaptiveRenderingBase(): string
    {
        return $this
            ->adaptiveResponseService
            ->getResponse()
            ->getRenderingBase();
    }

    public function adaptiveResponseSetPageContext(
        RenderPass $renderPass,
    ) {
        $renderPass->setCurrentContextRenderNode(
            $renderPass->layoutRenderNode->page
        );
    }

    public function adaptiveResponseRevertContext(RenderPass $renderPass)
    {
        $renderPass->revertCurrentContextRenderNode();
    }
}
