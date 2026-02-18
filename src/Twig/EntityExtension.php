<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyLoader\Rendering\RenderPass;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

class EntityExtension extends AbstractTemplateExtension
{
    public function __construct(
        private readonly ComponentsExtension $componentsExtension
    )
    {
    }

    public function getFunctions(): array
    {
        $options = [
            self::FUNCTION_OPTION_IS_SAFE => self::FUNCTION_OPTION_IS_SAFE_VALUE_HTML,
            self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true,
        ];

        return [
            new TwigFunction(
                'entity',
                [$this, 'entity'],
                $options
            ),
        ];
    }

    public function entity(
        Environment $twig,
        RenderPass $renderPass,
        mixed $entity,
        string $format,
        array $options = []
    ): string
    {
        return $this->componentsExtension->component(
            twig: $twig,
            renderPass: $renderPass,
            path: sprintf(
                '@front/components/entity/%s/%s',
                $entity::getSnakeShortClassName(),
                $format
            ),
            options: $options + [
                'entity' => $entity
            ]
        );
    }
}
