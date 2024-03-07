<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyHelpers\Helper\JsonHelper;

class AssetsRegistryService
{
    private array $manifest = [];

    private array $registry = [];

    private string $pathPublic;

    /**
     * @var string
     */
    private const CACHE_KEY_ASSETS_REGISTRY = 'assets_registry';

    public const DIR_BUILD = 'build/';

    public const DIR_PUBLIC = 'public/';

    public const FILE_MANIFEST = 'manifest.json';

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        KernelInterface $kernel,
        CacheInterface $cache,
    ) {
        $pathProject = $kernel->getProjectDir().'/';
        $this->pathPublic = $pathProject.self::DIR_PUBLIC;
        $pathBuild = $this->pathPublic.self::DIR_BUILD;
        $registry = null;

        // Assets registry is cached as manifest file may be unstable.
        if ($cache->hasItem(self::CACHE_KEY_ASSETS_REGISTRY)) {
            /** @var CacheItem $item */
            $item = $cache->getItem(self::CACHE_KEY_ASSETS_REGISTRY);
            $registry = $item->get();

            if ($registry) {
                $this->manifest = $registry;
            }
        }

        if (!$registry) {
            $cache->get(
                self::CACHE_KEY_ASSETS_REGISTRY,
                function() use
                (
                    $pathBuild
                ): array {
                    $this->manifest = JsonHelper::read(
                        $pathBuild.self::FILE_MANIFEST,
                        JSON_OBJECT_AS_ARRAY,
                        default: $this->manifest
                    );

                    return $this->manifest;
                }
            );
        }
    }

    public function assetExists(string $pathRelativeToPublic): bool
    {
        return isset($this->manifest[$pathRelativeToPublic]);
    }

    public function getRealPath(string $pathRelativeToPublic): string
    {
        return realpath($this->pathPublic.$this->manifest[$pathRelativeToPublic]);
    }

    public function addAsset(Asset $asset): void
    {
        $this->registry[$asset->type] = $this->registry[$asset->type] ?? [];

        if (!isset($this->registry[$asset->type][$asset->id])) {
            $this->registry[$asset->type][$asset->id] = $asset;
        }
    }

    public function getRegistry(): array
    {
        return $this->registry;
    }
}
