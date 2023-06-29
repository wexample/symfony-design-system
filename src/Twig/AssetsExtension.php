<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

class AssetsExtension extends AbstractExtension
{
    public function __construct(
        protected AssetsService $assetsService,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'assets_render_initial_aggregated',
                [
                    $this,
                    'assetsRenderInitialAggregated',
                ]
            ),
            new TwigFunction(
                'assets_type_filtered',
                [
                    $this,
                    'assetsTypeFiltered',
                ]
            ),
            new TwigFunction(
                'assets_preload_list',
                [
                    $this,
                    'assetsPreloadList',
                ]
            ),
        ];
    }

    public function assetsRenderInitialAggregated(string $pageName, string $type): string
    {
        return $this
            ->assetsService
            ->aggregateInitialAssets(
                $pageName,
                $type
            );
    }

    public function assetsTypeFiltered(
        string $contextType,
        string $assetType = null
    ): array {
        return $this
            ->assetsService
            ->assetsFiltered(
                $contextType,
                $assetType
            );
    }

    public function assetsPreloadList(string $ext): array
    {
        return $this->assetsService->assetsPreloadList($ext);
    }
}
