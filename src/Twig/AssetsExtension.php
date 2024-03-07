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
                'assets_build_tags',
                [
                    $this,
                    'assetsBuildTags',
                ]
            ),
        ];
    }

    public function assetsBuildTags(
        RenderPass $renderPass,
    ): array {
        return $this
            ->assetsService
            ->buildTags(
                $renderPass,
            );
    }
}
