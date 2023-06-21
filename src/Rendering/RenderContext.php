<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

class RenderContext
{
    public function __construct(
        public string $type,
        public ?string $name
    ) {
    }
}
