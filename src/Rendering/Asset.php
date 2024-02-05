<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Wexample\SymfonyHelpers\Helper\PathHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyHelpers\Helper\FileHelper;

class Asset extends RenderDataGenerator
{
    public const EXTENSION_CSS = 'css';

    public const EXTENSION_JS = 'js';

    public const EXTENSION_VUE = 'vue';

    public const ASSETS_EXTENSIONS = [
        Asset::EXTENSION_CSS,
        Asset::EXTENSION_JS,
    ];

    public const PRELOAD_BY_ASSET_TYPE = [
        self::EXTENSION_CSS => self::PRELOAD_AS_STYLE,
        self::EXTENSION_JS => self::PRELOAD_AS_SCRIPT,
    ];

    public const PRELOAD_AS_AUDIO = 'audio';

    public const PRELOAD_AS_DOCUMENT = 'document';

    public const PRELOAD_AS_EMBED = 'embed';

    public const PRELOAD_AS_FETCH = 'fetch';

    public const PRELOAD_AS_FONT = 'font';

    public const PRELOAD_AS_IMAGE = 'image';

    public const PRELOAD_AS_OBJECT = 'object';

    public const PRELOAD_AS_SCRIPT = 'script';

    public const PRELOAD_AS_STYLE = 'style';

    public const PRELOAD_AS_TRACK = 'track';

    public const PRELOAD_AS_WORKER = 'worker';

    public const PRELOAD_AS_VIDEO = 'video';

    public const PRELOAD_NONE = 'none';

    public const USAGE_ANIMATION = 'animation';

    public const USAGE_COLOR_SCHEME = 'color-scheme';

    public const USAGE_INITIAL = 'initial';

    public const USAGE_RESPONSIVE = 'responsive';

    public const USAGE_SHAPE = 'shape';

    public const USAGES = [
        self::USAGE_ANIMATION,
        self::USAGE_COLOR_SCHEME,
        self::USAGE_INITIAL,
        self::USAGE_RESPONSIVE,
        self::USAGE_SHAPE,
    ];

    public bool $active = false;

    public string $id;

    public bool $initialLayout = false;

    public string $media = 'screen';

    public string $path;

    public bool $preload = false;

    public bool $rendered = false;

    public ?string $responsive = null;

    public ?string $colorScheme = null;

    public string $type;

    public int $filesize;

    public function __construct(
        string $path,
        public AbstractRenderNode $renderData,
        string $basePath,
        public string $usage
    ) {
        $this->filesize = filesize($path);

        $info = pathinfo($path);
        $this->type = $info['extension'];

        $this->path = FileHelper::FOLDER_SEPARATOR.PathHelper::relativeTo(
                $path,
                $basePath
            );

        // Remove the base part before build/{type}/ folder.
        $pathWithoutExt = dirname($this->path).FileHelper::FOLDER_SEPARATOR.$info['filename'];

        $this->id = PathHelper::relativeTo(
            $pathWithoutExt,
            FileHelper::FOLDER_SEPARATOR.AssetsService::DIR_BUILD.
            $this->type.FileHelper::FOLDER_SEPARATOR
        );
    }

    /**
     * Used in twig rendering like "asset.preloadAs".
     */
    public function getPreloadAs(): ?string
    {
        if ($this->preload) {
            return self::PRELOAD_BY_ASSET_TYPE[$this->type];
        }

        return null;
    }

    public function getIsReadyForServerSideRendering(
        string $colorScheme,
        bool $useJs
    ): bool {
        if ($this->isServerSideRendered()) {
            return false;
        }

        if ($this->type === static::EXTENSION_JS) {
            return $useJs && !$this->responsive;
        }

        if ($this->type === static::EXTENSION_CSS) {
            if ($this->responsive) {
                // Responsive CSS are loaded in page when JS is disabled.
                return !$useJs;
            }

            if (null !== $this->colorScheme && $this->colorScheme !== $colorScheme) {
                // Non-base color schemes CSS are loaded using JS.
                return false;
            }
        }

        return true;
    }

    public function setServerSideRendered(bool $bool = true)
    {
        $this->active =
        $this->rendered =
        $this->initialLayout = $bool;
    }

    public function isServerSideRendered(): bool
    {
        return $this->active
            && $this->rendered
            && $this->initialLayout;
    }

    public function toRenderData(): array
    {
        return $this->serializeVariables([
            'active',
            'colorScheme',
            'filesize',
            'id',
            'initialLayout',
            'media',
            'path',
            'preload',
            'responsive',
            'rendered',
            'type',
            'usage',
        ]);
    }
}
