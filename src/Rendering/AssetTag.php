<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

class AssetTag
{
    private ?Asset $asset = null;
    private bool $canAggregate = false;
    private string $id;
    private string $media;
    private string $path;

    public function __construct(?Asset $asset = null)
    {
        $this->setAsset($asset);
    }

    public function canAggregate(): bool
    {
        return $this->canAggregate;
    }

    public function setCanAggregate(bool $canAggregate): void
    {
        $this->canAggregate = $canAggregate;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getMedia(): string
    {
        return $this->media;
    }

    public function setMedia(string $media): void
    {
        $this->media = $media;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getAsset(): Asset
    {
        return $this->asset;
    }

    public function setAsset(?Asset $asset): void
    {
        $this->asset = $asset;

        if ($asset) {
            $this->setId($asset->id);
            $this->setPath($asset->path);
            $this->setMedia($asset->media);
        }
    }
}
