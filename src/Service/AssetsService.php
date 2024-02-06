<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyHelpers\Helper\PathHelper;
use Wexample\SymfonyHelpers\Helper\JsonHelper;
use function array_merge;

class AssetsService
{
    /**
     * @var string
     */
    private const CACHE_KEY_ASSETS_REGISTRY = 'assets_registry';

    public const DISPLAY_BREAKPOINTS = [
        'xs' => 0,
        's' => 576,
        'm' => 768,
        'l' => 992,
        'xl' => 1200,
        'xxl' => 1400,
    ];

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
    }

    public function assetsDetect(
        string $path,
        AbstractRenderNode $context,
        array &$collection = []
    ): array {
        foreach (Asset::ASSETS_EXTENSIONS as $ext) {
            $collection[$ext] = array_merge(
                $collection[$ext] ?? [],
                $this->assetsDetectForType(
                    $path,
                    $ext,
                    $context,
                    true
                )
            );
        }

        return $collection;
    }

    /**
     * Return all assets for a given type, including suffixes like -s, -l, etc.
     */
    public function assetsDetectForType(
        string $renderNodeName,
        string $ext,
        AbstractRenderNode $renderNode,
    ): array {
        $assetPathFull = PathHelper::join([$ext, $renderNodeName.'.'.$ext]);
        $output = [];

        if ($asset = $this->addAsset(
            $assetPathFull,
            $renderNode
        )) {
            $output[] = $asset;
        }

        return $output;
    }

    /**
     * @throws Exception
     */
    public function addAsset(
        string $pathRelative,
        AbstractRenderNode $renderNode,
    ): ?Asset {
        $pathRelativeToPublic = self::DIR_BUILD.$pathRelative;

        if (!isset($this->registry[$pathRelativeToPublic])) {
            return null;
        }

        if (!isset($this->assetsLoaded[$pathRelative])) {
            $pathReal = realpath($this->pathPublic.$this->registry[$pathRelativeToPublic]);

            if (!$pathReal) {
                throw new Exception('Unable to find asset "'.$this->registry[$pathRelativeToPublic].'" from manifest for render node '.$renderNode->name);
            }

            $asset = new Asset(
                $pathReal,
                $this->pathPublic,
            );

            $this->assetsLoaded[$pathRelative] = $asset;
        } else {
            $asset = $this->assetsLoaded[$pathRelative];
        }

        return $this->assetsLoaded[$pathRelative];
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
}
