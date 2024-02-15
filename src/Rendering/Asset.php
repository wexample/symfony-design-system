<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Wexample\SymfonyDesignSystem\Service\AssetsRegistryService;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyHelpers\Helper\TextHelper;

class Asset extends RenderDataGenerator
{
    public const EXTENSION_CSS = 'css';

    public const EXTENSION_JS = 'js';

    public const ASSETS_EXTENSIONS = [
        Asset::EXTENSION_CSS,
        Asset::EXTENSION_JS,
    ];

    public bool $active = false;

    public string $id;

    public bool $initialLayout = false;

    public string $media = 'screen';

    public string $path;

    public ?string $colorScheme = null;

    public string $type;

    public ?string $responsive = null;

    public function __construct(
        string $pathRelativeToPublic,
        protected string $usage
    ) {
        $info = pathinfo($pathRelativeToPublic);
        $this->type = $info['extension'];
        // Add leading slash to load it from frontend.
        $this->path = FileHelper::FOLDER_SEPARATOR.$pathRelativeToPublic;
        $this->id = $this->buildId($this->path);
    }

    private function buildId($path): string
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

    public function toRenderData(): array
    {
        return $this->serializeVariables([
            'active',
            'colorScheme',
            'id',
            'initialLayout',
            'path',
            'responsive',
            'type',
            'usage',
        ]);
    }
}
