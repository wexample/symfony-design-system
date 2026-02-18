<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;
use Wexample\Helpers\Class\Traits\HasSnakeShortClassNameClassTrait;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

class EntityExtension extends AbstractTemplateExtension
{
    public function __construct(
        private readonly ComponentsExtension $componentsExtension
    ) {
    }

    public function getFunctions(): array
    {
        $options = [
            self::FUNCTION_OPTION_IS_SAFE => self::FUNCTION_OPTION_IS_SAFE_VALUE_HTML,
            self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true,
            self::FUNCTION_OPTION_NEEDS_CONTEXT => true,
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
        array $context,
        mixed $entity,
        string $format = 'bar',
        array $options = []
    ): string {
        $renderPass = $context['render_pass'] ?? null;
        if (! $renderPass) {
            throw new \RuntimeException('The "entity" Twig function requires "render_pass" in context.');
        }

        $entityName = $options['entity_name'] ?? $this->resolveEntityName($entity);
        $componentPath = $options['component'] ?? sprintf(
            '@front/components/entity/%s/%s',
            $entityName,
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

    private function resolveEntityName(mixed $entity): string
    {
        if (is_array($entity) && isset($entity['_entity']) && is_string($entity['_entity'])) {
            return $this->normalizeName($entity['_entity']);
        }

        if (is_object($entity)) {
            $shortName = (new \ReflectionClass($entity))->getShortName();
            return $this->normalizeName($shortName);
        }

        throw new \InvalidArgumentException(
            'Unable to resolve entity name. Provide an object entity or options["entity_name"].'
        );
    }

    private function normalizeName(string $name): string
    {
        $normalized = preg_replace('/Entity$/', '', trim($name)) ?? '';
        $normalized = preg_replace('/([a-z])([A-Z])/', '$1-$2', $normalized) ?? '';
        $normalized = strtolower($normalized);

        if ($normalized === '') {
            throw new \InvalidArgumentException('Entity name cannot be empty.');
        }

        return $normalized;
    }
}
