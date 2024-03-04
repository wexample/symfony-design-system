<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AssetsAggregationService;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

class AssetsAggregationExtension extends AbstractExtension
{
    public function __construct(
        protected AssetsAggregationService $assetsAggregationService,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'assets_aggregation_render_initial',
                [
                    $this,
                    'assetsAggregationRenderInitial',
                ]
            ),
        ];
    }

    public function assetsAggregationRenderInitial(
        RenderPass $renderPass,
        string $pageName,
        string $type
    ): string {
        return $this
            ->assetsAggregationService
            ->aggregateInitialAssets(
                $renderPass,
                $pageName,
                $type
            );
    }
}
