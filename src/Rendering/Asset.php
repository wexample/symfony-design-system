<?php

namespace Wexample\SymfonyDesignSystem\Rendering;
use Wexample\SymfonyHelpers\Helper\PathHelper;
use Wexample\SymfonyHelpers\Helper\FileHelper;

class Asset extends RenderDataGenerator
{
    public const EXTENSION_CSS = 'css';

    public const EXTENSION_JS = 'js';

    public const ASSETS_EXTENSIONS = [
        Asset::EXTENSION_CSS,
        Asset::EXTENSION_JS,
    ];

    public const USAGE_INITIAL = 'initial';

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
    }

    public function toRenderData(): array
    {
        return $this->serializeVariables([
            'path',
        ]);
    }
}
