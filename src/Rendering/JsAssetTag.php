<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Wexample\PhpHtml\Dom\ScriptTag;
use Wexample\SymfonyDesignSystem\Rendering\Traits\AssetTagTrait;

class JsAssetTag extends ScriptTag implements AssetTagInterface
{
    use AssetTagTrait;

    public function __construct(?Asset $asset = null)
    {
        $this->initAssetTag($asset);
    }

    public function getSrc(): ?string
    {
        return $this->getAttr('src');
    }

    public function setSrc(?string $src): static
    {
        if ($src === null) {
            unset($this->attributes['src']);
            return $this;
        }

        return parent::setSrc($src);
    }

    protected function setDestinationPath(?string $path): static
    {
        return $this->setSrc($path);
    }

    protected function getDestinationPath(): ?string
    {
        return $this->getSrc();
    }
}
