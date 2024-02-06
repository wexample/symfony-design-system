<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Rendering\RenderDataGenerator;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AssetsService;

abstract class AbstractRenderNode extends RenderDataGenerator
{
    public array $assets = AssetsService::ASSETS_DEFAULT_EMPTY;

    public bool $hasAssets = true;

    public string $name;

    abstract public function getContextType(): string;

    public function init(
        RenderPass $renderPass,
        string $name,
    ): void {
        $this->name = $name;

        $renderPass->registerRenderNode($this);
    }
}
