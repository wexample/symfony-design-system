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

    protected function pathAttribute(): string
    {
        return 'href';
    }
}
