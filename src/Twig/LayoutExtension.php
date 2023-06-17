<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Exception;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Wexample\SymfonyDesignSystem\Service\LayoutService;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

class LayoutExtension extends AbstractExtension
{
    public const LAYOUT_NAME_DEFAULT = VariableHelper::DEFAULT;

    public function __construct(
        private readonly AdaptiveResponseService $adaptiveResponseService,
        private readonly LayoutService $layoutService,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'layout_init',
                [
                    $this,
                    'layoutInit',
                ],
                [
                    self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true,
                ]
            ),
            new TwigFunction(
                'layout_render_initial_data',
                [
                    $this,
                    'layoutRenderInitialData',
                ],
                [
                    self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true,
                ]
            ),
        ];
    }

    /**
     * @throws Exception
     */
    public function layoutInit(
        Environment $twig,
        ?string $layoutName,
        string $colorScheme,
        bool $useJs = true,
    ): void {
        $this->layoutService->layoutInitInitial(
            $twig,
            $layoutName ?: VariableHelper::DEFAULT,
            $colorScheme,
            $useJs
        );
    }

    public function layoutRenderInitialData(): array
    {
        return $this
            ->adaptiveResponseService
            ->renderPass
            ->layoutRenderNode
            ->toRenderData();
    }
}
