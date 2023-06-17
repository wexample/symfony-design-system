<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Service\JsService;

class AdaptiveResponseExtension extends AbstractExtension
{
    /**
     * CommonExtension constructor.
     */
    public function __construct(
        protected AdaptiveResponseService $adaptiveResponseService,
        protected RequestStack $requestStack,
        protected JsService $jsService
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
                'adaptive_response_set_context',
                [
                    $this,
                    'adaptiveResponseSetContext',
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
            new TwigFunction(
                'var_js',
                [
                    $this,
                    'varJs',
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

    public function adaptiveResponseSetContext(
        string $renderNodeType,
        ?string $renderNodeName,
    ) {
        $this
            ->adaptiveResponseService
            ->renderPass
            ->setCurrentContextRenderNodeByTypeAndName(
                $renderNodeType,
                $renderNodeName
            );
    }

    public function adaptiveResponseRevertContext()
    {
        $this
            ->adaptiveResponseService
            ->renderPass
            ->revertCurrentContextRenderNode();
    }

    public function varJs(
        string $name,
        mixed $value
    ): void {
        $this->jsService->varJs($name, $value);
    }
}
