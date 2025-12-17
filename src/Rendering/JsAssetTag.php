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

    protected function pathAttribute(): string
    {
        return 'src';
    }
}
