<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Exception;
use JetBrains\PhpStorm\Pure;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Wexample\SymfonyDesignSystem\Helper\ColorSchemeHelper;
use Wexample\SymfonyDesignSystem\Helper\DomHelper;
use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyHelpers\Helper\JsonHelper;
use Wexample\SymfonyHelpers\Helper\VariableHelper;
use function array_merge;
use function array_reverse;
use function basename;
use function dirname;
use function file_get_contents;
use function implode;
use function realpath;
use function str_replace;

class AssetsService
{
    /**
     * @var string
     */
    private const CACHE_KEY_ASSETS_REGISTRY = 'assets_registry';

    public const COLOR_SCHEME_DIR = 'color_schemes';

    /**
     * @var array|Asset[][]
     */
    public const ASSETS_DEFAULT_EMPTY = [
        Asset::EXTENSION_CSS => [],
        Asset::EXTENSION_JS => [],
    ];

    public const DIR_BUILD = 'build/';

    public const DIR_PUBLIC = 'public/';

    public const FILE_MANIFEST = 'manifest.json';

    private array $aggregationHash = [];

    private array $assets = self::ASSETS_DEFAULT_EMPTY;

    protected array $assetsLoaded = [];

    private string $pathProject;

    private string $pathBuild;

    private array $registry = [];

    private string $pathPublic;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        KernelInterface $kernel,
        private readonly AdaptiveResponseService $adaptiveResponseService,
        CacheInterface $cache
    ) {
        $this->pathProject = $kernel->getProjectDir().'/';
        $this->pathPublic = $this->pathProject.self::DIR_PUBLIC;
        $this->pathBuild = $this->pathPublic.self::DIR_BUILD;
        $registry = null;

        // Assets registry is cached as manifest file may be unstable.
        if ($cache->hasItem(self::CACHE_KEY_ASSETS_REGISTRY)) {
            /** @var CacheItem $item */
            $item = $cache->getItem(self::CACHE_KEY_ASSETS_REGISTRY);
            $registry = $item->get();

            if ($registry) {
                $this->registry = $registry;
            }
        }

        if (!$registry) {
            $cache->get(
                self::CACHE_KEY_ASSETS_REGISTRY,
                function(): array {
                    $this->registry = JsonHelper::read(
                        $this->pathBuild.self::FILE_MANIFEST,
                        JSON_OBJECT_AS_ARRAY,
                        default: $this->registry
                    );

                    return $this->registry;
                }
            );
        }

        $this->adaptiveResponseService->addRenderEventListener(
            $this
        );
    }

    public function replacePreloadPlaceholder(
        string $rendered
    ): string {
        if (str_contains($rendered, RenderingHelper::PLACEHOLDER_PRELOAD_TAG)) {
            $renderPass = $this->adaptiveResponseService->renderPass;
            $html = '';

            if ($renderPass->getEnableAggregation()) {
                $pageName = $this->adaptiveResponseService->renderPass->pageName;

                $html .= $this->buildPreloadTag(
                    $this->buildAggregatedPublicPath($pageName, Asset::EXTENSION_CSS),
                    Asset::EXTENSION_CSS
                );

                $html .= $this->buildPreloadTag(
                    $this->buildAggregatedPublicPath($pageName, Asset::EXTENSION_JS),
                    Asset::EXTENSION_JS
                );
            } else {
                $html .= $this->buildPreloadTagsForAssetsType(
                    Asset::EXTENSION_CSS
                );

                $html .= $this->buildPreloadTagsForAssetsType(
                    Asset::EXTENSION_JS
                );
            }

            return str_replace(
                RenderingHelper::PLACEHOLDER_PRELOAD_TAG,
                $html,
                $rendered
            );
        }

        return $rendered;
    }

    protected function buildPreloadTagsForAssetsType(string $type): string
    {
        $output = [];

        $assets = $this->getServerSideRenderedAssets(
            $type,
            false
        );

        foreach ($assets as $path) {
            $output[] = $this->buildPreloadTag(
                $path,
                $type
            );
        }

        return implode(PHP_EOL, $output);
    }

    protected function buildPreloadTag(
        string $path,
        string $type
    ): string {
        return DomHelper::buildTag(
            DomHelper::TAG_LINK,
            [
                'rel' => VariableHelper::PRELOAD,
                'href' => $path,
                'as' => Asset::PRELOAD_BY_ASSET_TYPE[$type],
            ]
        );
    }
    
    public function assetsDetect(
        AbstractRenderNode $contextRenderNode,
        array &$collection = []
    ): array {
        foreach (Asset::ASSETS_EXTENSIONS as $ext) {
            $collection[$ext] = array_merge(
                $collection[$ext] ?? [],
                $this->assetsDetectForType(
                    $ext,
                    $contextRenderNode,
                )
            );
        }

        return $collection;
    }

    /**
     * Return all assets for a given type, including suffixes like -s, -l, etc.
     */
    public function assetsDetectForType(
        string $ext,
        AbstractRenderNode $renderNode,
        bool $searchColorScheme
    ): array {
        $output = [];

        // Prevent infinite loops.
        if ($searchColorScheme) {
            // Add color scheme assets.
            $basename = basename($renderNodeName);
            $dirname = dirname($renderNodeName);
            foreach (ColorSchemeHelper::SCHEMES as $colorSchemeName) {
                // Color scheme's version should be place in :
                // colors/[dark|light|...]/same/path
                $colorSchemePageName = implode(
                    FileHelper::FOLDER_SEPARATOR,
                    [
                        self::COLOR_SCHEME_DIR,
                        $colorSchemeName,
                        $dirname,
                        $basename,
                    ]
                );

                $assets = $this->assetsDetectForType(
                    $colorSchemePageName,
                    $ext,
                    $renderNode,
                    false
                );

                /** @var Asset $asset */
                foreach ($assets as $asset) {
                    $asset->colorScheme = $colorSchemeName;
                    $output[] = $asset;
                }
            }
        }

        return $output;
    }

    /**
     * @throws Exception
     */
    public function addAsset(
        AbstractRenderNode $renderNode,
        string $ext
    ): ?Asset {
        $pathRelativeToPublic = $renderNode->buildBuiltPublicAssetPath($ext);

        if (!isset($this->registry[$pathRelativeToPublic])) {
            return null;
        }

        if (!isset($this->assetsLoaded[$pathRelativeToPublic])) {
            $pathReal = realpath($this->pathPublic.$this->registry[$pathRelativeToPublic]);

            if (!$pathReal) {
                throw new Exception('Unable to find asset "'.$this->registry[$pathRelativeToPublic].'" from manifest for render node '.$renderNode->name);
            }

            $asset = new Asset(
                $pathReal,
                $this->pathPublic,
            );

            $this->assetsLoaded[$pathRelativeToPublic] = $asset;
        } else {
            $asset = $this->assetsLoaded[$pathRelative];
        }

        $this->assets[$asset->type][] = $asset;

        return $this->assetsLoaded[$pathRelativeToPublic];
    }

    public function assetsPreload(
        array $assets,
        string $colorScheme,
        bool $useJs
    ) {
        /** @var Asset $asset */
        foreach ($assets as $asset) {
            if ($asset->getIsReadyForServerSideRendering($colorScheme, $useJs)) {
                $asset->preload = true;
            }
        }
    }

    public function assetsPreloadList(string $ext): array
    {
        $assets = $this->assets[$ext];
        $output = [];

        /** @var Asset $asset */
        foreach ($assets as $asset) {
            if ($asset->preload) {
                $output[] = $asset;
            }
        }

        return $output;
    }

    public function assetsFiltered(
        RenderPass $renderPass,
        string $contextType,
        string $assetType = null
    ): array {
        $assets = [];

        /** @var AbstractRenderNode $renderNode */
        foreach ($renderPass->registry[$contextType] as $renderNode) {
            $assets = array_merge(
                $assets,
                $renderNode->assets[$assetType]
            );
        }

        return $assets;
    }

    public function renderEventPostRender(array &$options)
    {
        $options['rendered'] = $this->replacePreloadPlaceholder(
            $options['rendered']
        );
    }
}
