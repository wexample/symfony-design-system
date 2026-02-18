<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;
use Wexample\Helpers\Class\Traits\HasSnakeShortClassNameClassTrait;
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

    /**
     * @param Environment $twig
     * @param array $context
     * @param HasSnakeShortClassNameClassTrait $entity
     * @param string $format
     * @param array $options
     * @return string
     * @throws \Exception
     */
    public function entity(
        Environment $twig,
        RenderPass $renderPass,
        mixed $entity,
        string $format = 'bar',
        array $options = []
    ): string
    {
        $componentPath = $options['component'] ?? sprintf(
            '@front/components/entity/%s/%s',
            $entity::getSnakeShortClassName(),
            strtolower(trim($format))
        );

        return $this->componentsExtension->component(
            $twig,
            $renderPass,
            $componentPath,
            [
                'entity' => $entity,
                'options' => $options['component_options'] ?? [],
            ]
        );
    }
}
