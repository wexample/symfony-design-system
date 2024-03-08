<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Wexample\SymfonyDesignSystem\Rendering\Traits\WithDomIdFromTemplateAbstractPathTrait;
use Wexample\SymfonyDesignSystem\Service\AssetsRegistryService;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyHelpers\Helper\TextHelper;

class Asset extends RenderDataGenerator
{
    use WithDomIdFromTemplateAbstractPathTrait;

    public const EXTENSION_CSS = 'css';

    public const EXTENSION_JS = 'js';

    public const ASSETS_EXTENSIONS = [
        Asset::EXTENSION_CSS,
        Asset::EXTENSION_JS,
    ];

    public bool $active = false;

    public bool $initialLayout = false;

    public string $media = 'screen';

    public string $path;

    public string $type;

    public array $usages = [];

    public function __construct(
        string $pathInManifest,
        protected string $usage,
        protected string $context
    ) {
        $info = pathinfo($pathInManifest);
        $this->type = $info['extension'];
        // Add leading slash to load it from frontend.
        $this->path = FileHelper::FOLDER_SEPARATOR.$pathInManifest;
        // Same as render node id
        $this->setTemplateAbstractPath(
            $this->buildTemplateAbstractPath($this->path)
        );
    }

    private function buildTemplateAbstractPath(string $path): string
    {
        $path = TextHelper::trimFirstChunk(
            FileHelper::removeExtension($path),
            AssetsRegistryService::DIR_BUILD
        );

        $explode = explode('/', $path);

        if (current($explode) == 'app') {
            $slicePos = 1;
            $bundleName = current($explode);
        } else {
            $slicePos = 2;
            $bundleName = implode('/', array_slice($explode, 0, $slicePos));
        }

        return $bundleName.'::'.implode('/', array_slice($explode, $slicePos + 1));
    }

    public function setServerSideRendered(bool $bool = true)
    {
        $this->active =
        $this->initialLayout = $bool;
    }

    public function getUsage(): string
    {
        return $this->usage;
    }

    public function isServerSideRendered(): bool
    {
        return $this->active
            && $this->initialLayout;
    }

    public function toRenderData(): array
    {
        return $this->serializeVariables([
            'active',
            'context',
            'domId',
            'initialLayout',
            'path',
            'templateAbstractPath',
            'type',
            'usage',
            'usages',
        ]);
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function getDomPrefix(): string
    {
        return $this->type;
    }
}
