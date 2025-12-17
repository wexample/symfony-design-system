<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Wexample\PhpHtml\Dom\HtmlTag;

// TODO Should disappear, if some shared property are used across CssAsset and JsAsset, create a trait in Rendering\Traits

class AssetTag extends HtmlTag
{
    protected ?Asset $asset = null;
    protected bool $canAggregate = false;
    protected string $usageName;
    protected string $context;

    public function __construct(?Asset $asset = null)
    {
        if ($asset) {
            $this->fromAsset($asset);
        }
    }

    public function fromAsset(Asset $asset): static
    {
        $this->asset = $asset;

        $this->id($asset->getDomId())
            ->attr('media', $asset->getMedia())
            ->attr($this->pathAttribute(), $asset->getPath());

        $this->usageName = $asset->getUsage();
        $this->context = $asset->getContext();

        return $this;
    }

    abstract protected function pathAttribute(): string;

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
        return $this->getAttribute('media');
    }

    public function setMedia(?string $media): static
    {
        return $this->attr('media', $media);
    }

    public function getPath(): ?string
    {
        return $this->getAttribute($this->pathAttribute());
    }

    public function setPath(?string $path): static
    {
        return $this->attr($this->pathAttribute(), $path);
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
