<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\TwigFunction;
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
        ];
    }

    public function assetsTypeFiltered(
        RenderPass $renderPass,
        string $contextType,
        string $assetType = null
    ): array {
        return $this
            ->assetsService
            ->assetsFiltered(
                $renderPass,
                $contextType,
                $assetType
            );
    }
}
