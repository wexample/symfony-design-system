<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyHelpers\Helper\PathHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderDataGenerator;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AssetsService;

abstract class AbstractRenderNode extends RenderDataGenerator
{
    public array $assets = AssetsService::ASSETS_DEFAULT_EMPTY;

    protected string $id;

    public bool $hasAssets = true;

    public string $name;

    abstract public function getContextType(): string;

    public function init(
        RenderPass $renderPass,
        string $name,
    ): void {
        $this->name = $name;
        $this->id = $this->getContextType().'-'
            .str_replace('/', '-', $this->name)
            .'-'.uniqid();

        $renderPass->registerRenderNode($this);
    }

    public function toRenderData(): array
    {
        return [
            'assets' => [
                Asset::EXTENSION_CSS => $this->arrayToRenderData($this->assets[Asset::EXTENSION_CSS]),
                Asset::EXTENSION_JS => $this->arrayToRenderData($this->assets[Asset::EXTENSION_JS]),
            ],
            'name' => $this->name,
        ];
    }

    public function buildBuiltPublicAssetPath(string $ext): string
    {
        $nameParts = explode('::', $this->name);

        return AssetsService::DIR_BUILD . PathHelper::join([$nameParts[0], $ext, $nameParts[1].'.'.$ext]);
    }
}
