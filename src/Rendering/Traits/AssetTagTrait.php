<?php

namespace Wexample\SymfonyDesignSystem\Rendering\Traits;

use Wexample\SymfonyDesignSystem\Rendering\Asset;

trait AssetTagTrait
{
    protected ?Asset $asset = null;
    protected bool $canAggregate = false;
    protected string $usageName = '';
    protected string $context = '';

    protected function initAssetTag(?Asset $asset = null): void
    {
        if ($asset) {
            $this->fromAsset($asset);
        }
    }

    abstract protected function setDestinationPath(?string $path): static;

    abstract protected function getDestinationPath(): ?string;

    public function fromAsset(Asset $asset): static
    {
        $this->asset = $asset;

        $this->setId($asset->getDomId())
            ->setMedia($asset->getMedia())
            ->setPath($asset->getPath());

        $this->usageName = $asset->getUsage();
        $this->context = $asset->getContext();

        return $this;
    }

    public function canAggregate(): bool
    {
        return $this->canAggregate;
    }

    public function setCanAggregate(bool $canAggregate): static
    {
        $this->canAggregate = $canAggregate;
        return $this;
    }

    public function getMedia(): ?string
    {
        return $this->getAttr('media');
    }

    public function setMedia(?string $media): static
    {
        return $this->setAttr('media', $media);
    }

    public function getPath(): ?string
    {
        return $this->getDestinationPath();
    }

    public function setPath(?string $path): static
    {
        return $this->setDestinationPath($path);
    }

    public function getAsset(): ?Asset
    {
        return $this->asset;
    }

    public function setAsset(?Asset $asset): static
    {
        if ($asset) {
            $this->fromAsset($asset);
        }
        return $this;
    }

    public function getUsageName(): string
    {
        return $this->usageName;
    }

    public function setUsageName(string $usageName): static
    {
        $this->usageName = $usageName;
        return $this;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function setContext(string $context): static
    {
        $this->context = $context;
        return $this;
    }
}
