<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use function is_a;
use function is_object;

abstract class RenderDataGenerator
{

    abstract public function toRenderData(): array;

    public function serializeVariables(array $variables): array
    {
        $output = [];

        foreach ($variables as $variable) {
            $value = $this->$variable;

            if (!is_object($value)) {
                $output[$variable] = $value;
            } elseif (is_a($value, RenderDataGenerator::class)) {
                $output[$variable] = $value->toRenderData();
            }
        }

        return $output;
    }
}
