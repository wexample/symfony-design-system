<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\JsService;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

class VarExtension extends AbstractExtension
{
    public function __construct(
        protected JsService $jsService
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'var_export',
                [
                    $this,
                    'varExport',
                ]
            ),
                [
                    $this,
                    'varJs',
                ]
            ),
        ];
    }

    public function varJs(

    public function varExport(
        RenderPass $renderPass,
        string $name,
        mixed $value
    ): void {
        $this->jsService->varExport(
            $renderPass,
            $name,
            $value
        );
    }
}
