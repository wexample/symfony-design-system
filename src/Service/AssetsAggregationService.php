<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyHelpers\Helper\FileHelper;

class AssetsAggregationService
{
    public const DIR_BUILD = 'build/';

    public const DIR_PUBLIC = 'public/';

    private array $aggregationHash = [];

    private string $pathProject;

    private string $pathPublic;

    public function __construct(
        KernelInterface $kernel,
        readonly private AssetsRegistryService $assetsRegistryService
    ) {
        $this->pathProject = $kernel->getProjectDir().'/';
        $this->pathPublic = $this->pathProject.self::DIR_PUBLIC;
    }

    public function getServerSideRenderedAssets(
        string $type,
        bool $serverPath
    ): array {
        $basePath = '';
        if ($serverPath) {
            $basePath = rtrim(
                $this->pathPublic,
                FileHelper::FOLDER_SEPARATOR
            );
        }

        $assets = $this
            ->assetsRegistryService
            ->getServerSideRenderedAssets(
                $type
            );

        foreach ($assets as $asset) {
            $aggregatePaths[] = $basePath.$asset->path;
        }

        // Per type specific assets.
        if (Asset::EXTENSION_JS === $type) {
            $runtimePath = $basePath.FileHelper::FOLDER_SEPARATOR.'build/runtime.js';

            if (is_file($runtimePath)) {
                $aggregatePaths[] = $runtimePath;
            }
        }

        return $aggregatePaths;
    }

    public function aggregateInitialAssets(
        string $pageName,
        string $type
    ): string {
        $aggregatedFileName = $this->buildAggregatedPathFromPageName($pageName, $type);
        $output = '';

        $aggregatePaths = $this->getServerSideRenderedAssets(
            $type,
            true
        );

        $aggregated = [];
        foreach ($aggregatePaths as $path) {
            if (!isset($aggregated[$path])) {
                $aggregated[$path] = true;
                $output .=
                    PHP_EOL.'/* '.$path.' */ '.PHP_EOL
                    .file_get_contents($path);
            }
        }

        $this->aggregationHash[$type.'-'.$pageName] = FileHelper::fileWriteAndHash(
            $this->pathPublic.$aggregatedFileName,
            $output
        );

        return $this->buildAggregatedPublicPath(
            $pageName,
            $type
        );
    }

    protected function buildAggregatedPublicPath(
        string $pageName,
        string $type
    ): string {
        return FileHelper::FOLDER_SEPARATOR.
            $this->buildAggregatedPathFromPageName($pageName, $type)
            .(
            isset($this->aggregationHash[$type.'-'.$pageName])
                ? '?'.$this->aggregationHash[$type.'-'.$pageName]
                : ''
            );
    }

    protected function buildAggregatedPathFromPageName(
        string $pageName,
        string $type
    ): string {
        return self::DIR_BUILD.$type.'/'.$pageName.'.'.FileHelper::SUFFIX_AGGREGATED.'.'.$type;
    }
}
