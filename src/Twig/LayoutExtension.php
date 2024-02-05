<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Exception;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Wexample\SymfonyDesignSystem\Service\LayoutService;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

class LayoutExtension extends AbstractExtension
{
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
        ];
    }

    /**
     * @throws Exception
     */
    public function layoutInit(
        Environment $twig,
        ?string $layoutName,
    ): void {
        $this->layoutService->layoutInitInitial(
            $twig,
            $layoutName,
        );
    }
}
