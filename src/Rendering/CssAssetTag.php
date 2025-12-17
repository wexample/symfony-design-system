<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Wexample\PhpHtml\Dom\LinkTag;
use Wexample\SymfonyDesignSystem\Rendering\Traits\AssetTagTrait;

class CssAssetTag extends LinkTag implements AssetTagInterface
{
    use AssetTagTrait;

    public function __construct(?Asset $asset = null)
    {
        parent::__construct();
        $this->initAssetTag($asset);
    }

    public function getHref(): ?string
    {
        return $this->getAttr('href');
    }

    public function setHref(?string $href): static
    {
        if ($href === null) {
            unset($this->attributes['href']);
            return $this;
        }

        return parent::setHref($href);
    }

    protected function setDestinationPath(?string $path): static
    {
        return $this->setHref($path);
    }

    protected function getDestinationPath(): ?string
    {
        return $this->getHref();
    }
}
