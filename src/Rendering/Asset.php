<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyHelpers\Helper\PathHelper;
use Wexample\SymfonyHelpers\Helper\TextHelper;

class Asset extends RenderDataGenerator
{
    public const EXTENSION_CSS = 'css';

    public const EXTENSION_JS = 'js';

    public const ASSETS_EXTENSIONS = [
        Asset::EXTENSION_CSS,
        Asset::EXTENSION_JS,
    ];

    public const USAGE_INITIAL = 'initial';

    public bool $active = false;

    public string $id;

    public string $media = 'screen';

    public string $path;

    public string $type;

    public function __construct(
        string $path,
        string $basePath,
    ) {
        $info = pathinfo($path);
        $this->type = $info['extension'];

        $this->path = FileHelper::FOLDER_SEPARATOR.PathHelper::relativeTo(
                $path,
                $basePath
            );

        $this->id = $this->buildId($this->path);
    }

    private function buildId($path): string
    {
        $path = TextHelper::trimFirstChunk(
            FileHelper::removeExtension($path),
            AssetsService::DIR_BUILD
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
    }

    public function toRenderData(): array
    {
        return $this->serializeVariables([
            'path',
        ]);
    }
}
