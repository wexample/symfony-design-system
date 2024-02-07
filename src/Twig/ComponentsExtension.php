<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Exception;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\ComponentService;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

class ComponentsExtension extends AbstractExtension
{
    public function __construct(
        protected ComponentService $componentService,
    ) {
    }

    public function getFunctions(): array
    {
        $initOptions = [
            self::FUNCTION_OPTION_IS_SAFE => self::FUNCTION_OPTION_IS_SAFE_VALUE_HTML,
            self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true,
        ];

        return [
            new TwigFunction(
                'component_init_parent',
                [
                    $this,
                    'componentInitParent',
                ],
                $initOptions
            ),
        ];
    }

    /**
     * @throws Exception
     */
    public function componentInitParent(
        Environment $twig,
        RenderPass $renderPass,
        string $name,
        array $options = []
    ): string {
        return $this
            ->componentService
            ->componentInitParent(
                $twig,
                $renderPass,
                $name,
                $options
            )->renderTag();
    }
}
