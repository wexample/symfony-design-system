<?php

namespace Wexample\SymfonyDesignSystem\Service\Traits;

trait PostRenderViewServiceTrait
{
    abstract public function onPostRenderView(
        string $view,
        array $parameters,
        string $rendered
    ): string;
}