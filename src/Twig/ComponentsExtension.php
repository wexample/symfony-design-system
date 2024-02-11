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
                'component',
                [
                    $this,
                    'componentInitPrevious',
                ],
                $initOptions
            ),
            new TwigFunction(
                'component_init_class',
                [
                    $this,
                    'componentInitClass',
                ],
                $initOptions
            ),
            new TwigFunction(
                'component_init_parent',
                [
                    $this,
                    'componentInitParent',
                ],
                $initOptions
            ),
            new TwigFunction(
                'component_init_previous',
                [
                    $this,
                    'componentInitPrevious',
                ],
                $initOptions
            ),
        ];
    }

    /**
     * @throws Exception
     */
    public function componentInitPrevious(
        Environment $twig,
        RenderPass $renderPass,
        string $name,
        array $options = []
    ): string {
        return $this->componentService->componentInitPrevious(
            $twig,
            $renderPass,
            $name,
            $options
        )->renderTag();
    }

    /**
     * Init a components and provide a class name to retrieve dom element.
     *
     * @throws Exception
     */
    public function componentInitClass(
        Environment $twig,
        RenderPass $renderPass,
        string $name,
        array $options = []
    ): string {
        return $this
            ->componentService
            ->componentInitClass(
                $twig,
                $renderPass,
                $name,
                $options
            )->renderCssClasses();
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
