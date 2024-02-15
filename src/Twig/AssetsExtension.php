<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
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
                'assets_type_filtered',
                [
                    $this,
                    'assetsTypeFiltered',
                ]
            ),
            new TwigFunction(
                'assets_is_ready_for_render',
                [
                    $this,
                    'assetsIsReadyForRender',
                ]
            ),
            new TwigFunction(
                'assets_non_empty_usages',
                [
                    $this,
                    'assetsNonEmptyUsages',
                ]
            ),
        ];
    }

    public function assetsTypeFiltered(
        RenderPass $renderPass,
        string $contextType,
        string $usage,
        string $assetType = null
    ): array {
        return $this
            ->assetsService
            ->assetsFiltered(
                $renderPass,
                $contextType,
                $usage,
                $assetType
            );
    }

    public function assetsIsReadyForRender(
        RenderPass $renderPass,
        Asset $asset
    ): bool {
        return $this
            ->assetsService
            ->assetIsReadyForRender(
                $asset,
                $renderPass,
            );
    }

    public function assetsNonEmptyUsages(): array
    {
        $keys = [];
        $usages = $this->assetsService->getAssetsUsages();

        foreach ($usages as $name => $usage) {
            if ($usage->hasAsset()) {
                $keys[] = $name;
            }
        }

        return $keys;
    }
}
