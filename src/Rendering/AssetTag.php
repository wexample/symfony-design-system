<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Wexample\PhpHtml\Dom\HtmlTag;
use Wexample\SymfonyDesignSystem\Rendering\Traits\AssetTagTrait;

/**
 * @deprecated Use CssAssetTag or JsAssetTag directly.
 */
abstract class AssetTag extends HtmlTag implements AssetTagInterface
{
    use AssetTagTrait;
}
