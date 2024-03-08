<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Exception;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\VueService;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

class VueExtension extends AbstractExtension
{
    final public const TEMPLATE_FILE_EXTENSION = '.vue.twig';

    public function __construct(
        private readonly VueService $vueService
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'vue',
                [
                    $this,
                    'vue',
                ],
                [
                    self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true,
                    self::FUNCTION_OPTION_IS_SAFE => [self::FUNCTION_OPTION_HTML],
                ]
            ),
            new TwigFunction(
                'vue_render_templates',
                [
                    $this,
                    'vueRenderTemplates',
                ]
            ),
        ];
    }

    /**
     * @throws Exception
     */
    public function vue(
        Environment $env,
        RenderPass $renderPass,
        string $path,
        ?array $props = [],
        ?array $twigContext = []
    ): string {
        return $this->vueService->vueRender(
            $env,
            $renderPass,
            $path,
            $props,
            $twigContext
        );
    }

    public function vueRenderTemplates(): string
    {
        // Add vue js templates.
        return implode('', $this->vueService->renderedTemplates);
    }
}
