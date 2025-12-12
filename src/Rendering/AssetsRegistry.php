<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use JsonSerializable;
use RuntimeException;

class AssetsRegistry implements JsonSerializable
{
    public const DIR_PUBLIC = 'public/';
    public const DIR_BUILD = 'build/';
    public const FILE_MANIFEST = 'manifest.json';

    private array $manifest = [];

    private string $pathPublic;

    public function __construct(
        string $projectDir
    )
    {
        $this->pathPublic = rtrim($projectDir, '/') . '/' . self::DIR_PUBLIC;
        $this->loadManifest();
    }

    protected function loadManifest(): void
    {
        $manifestPath = $this->pathPublic . self::DIR_BUILD . self::FILE_MANIFEST;

        if (!is_file($manifestPath)) {
            return;
        }

        $content = file_get_contents($manifestPath);

        if ($content === false) {
            throw new RuntimeException(sprintf('Unable to read assets manifest "%s".', $manifestPath));
        }

        $data = json_decode($content, true);

        if (!is_array($data)) {
            throw new RuntimeException(sprintf('Invalid JSON manifest "%s".', $manifestPath));
        }

        $this->manifest = $data;
    }

    public function assetExists(string $pathInManifest): bool
    {
        return isset($this->manifest[$pathInManifest]);
    }

    public function getBuiltPath(string $pathInManifest): ?string
    {
        return $this->manifest[$pathInManifest] ?? null;
    }

    public function getRealPath(string $pathInManifest): ?string
    {
        $builtPath = $this->getBuiltPath($pathInManifest);

        if (!$builtPath) {
            return null;
        }

        return realpath($this->pathPublic . $builtPath) ?: null;
    }

    public function addAsset(Asset $asset): void
    {
        $type = $asset->getType();
        $this->registry[$type] = $this->registry[$type] ?? [];
        $templateName = $asset->getView();

        if (! isset($this->registry[$type][$templateName])) {
            $this->registry[$type][$templateName] = $asset;
        }
    }

    public function toArray(): array
    {
        $output = [];
        foreach ($this->registry as $type => $assets) {
            $output[$type] = [];
            /** @var Asset $asset */
            foreach ($assets as $id => $asset) {
                $output[$type][$id] = $asset->toArray();
            }
        }

        return $output;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
