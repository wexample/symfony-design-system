<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Twig\TwigFunction;

class AdaptiveResponseExtension extends AbstractExtension
{
    /**
     * CommonExtension constructor.
     */
    public function __construct(
        protected AdaptiveResponseService $adaptiveResponseService,
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
}
