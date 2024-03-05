<?php

namespace Wexample\SymfonyDesignSystem\Service;

use ReflectionClass;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Wexample\SymfonyApi\Api\Dto\EntityDto;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyHelpers\Entity\Interfaces\AbstractEntityInterface;
use function class_exists;
use function is_array;
use function is_object;
use function is_subclass_of;

class JsService
{
    /**
     * CommonExtension constructor.
     */
    public function __construct(
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function varJs(
        RenderPass $renderPass,
        string $name,
        mixed $value,
    ): void {
        $renderPass
            ->getCurrentContextRenderNode()
            ->vars[$name] = $this->serializeValue($value);
    }

    /**
     * @throws ExceptionInterface
     */
    public function serializeValue(
        mixed $item,
        $context = null
    ): mixed {
        // Recursive exploration.
        if (is_array($item)) {
            return $this->serializeArray($item, $context);
        }

        // Convert entities.
        if ($entityDto = $this->serializeEntity($item, $context)) {
            return $entityDto;
        }

        return $item;
    }

    /**
     * @throws ExceptionInterface
     */
    public function serializeArray(
        array $array,
        array $context = null
    ): array {
        $output = [];

        foreach ($array as $key => $item) {
            $output[$key] = $this->serializeValue($item, $context);
        }

        return $output;
    }

    /**
     * @throws ExceptionInterface
     */
    public function serializeEntity(
        $value,
        ?array $context = [
            'displayFormat' => EntityDto::DISPLAY_FORMAT_DEFAULT,
        ]
    ): ?array {
        if (is_object($value)
            && is_subclass_of(
                $value,
                AbstractEntityInterface::class
            )) {
            $objectValue = $value;
            // Find if class is an entity and have an API Dto object.
            $dtoClassName = '\\App\\Api\\Dto\\'.
                (new ReflectionClass($objectValue))->getShortName();

            if (!isset($context['collection_operation_name'])) {
                $context['collection_operation_name'] = 'twig_serialize_entity';
            }

            if (class_exists($dtoClassName)
                && is_subclass_of($dtoClassName, EntityDto::class)) {
                return $this->normalizer->normalize(
                    $objectValue,
                    'jsonld',
                    $context
                );
            }
        }

        return null;
    }
}
