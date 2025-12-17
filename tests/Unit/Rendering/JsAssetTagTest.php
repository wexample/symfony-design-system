<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Rendering;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\JsAssetTag;

class JsAssetTagTest extends TestCase
{
    public function testSrcGettersAndDestinationPath(): void
    {
        $tag = new JsAssetTag();

        $this->assertNull($tag->getSrc());
        $this->assertNull($this->invokeGetDestinationPath($tag));

        $tag->setSrc('build/app.js');

        $this->assertSame('build/app.js', $tag->getSrc());
        $this->assertSame('build/app.js', $this->invokeGetDestinationPath($tag));

        $tag->setSrc(null);
        $this->assertNull($tag->getSrc());
    }

    public function testAssetAndUsageContextAccessors(): void
    {
        $asset = new Asset('build/app.js', 'bundle/view', 'usage', Asset::CONTEXT_PAGE);
        $tag = new JsAssetTag();

        $this->assertNull($tag->getAsset());

        $tag->setUsageName('custom');
        $tag->setContext(Asset::CONTEXT_COMPONENT);
        $this->assertSame('custom', $tag->getUsageName());
        $this->assertSame(Asset::CONTEXT_COMPONENT, $tag->getContext());

        $tag->setAsset($asset);

        $this->assertSame($asset, $tag->getAsset());
        $this->assertSame($asset->getUsage(), $tag->getUsageName());
        $this->assertSame($asset->getContext(), $tag->getContext());
    }

    private function invokeGetDestinationPath(JsAssetTag $tag): ?string
    {
        $ref = new \ReflectionMethod($tag, 'getDestinationPath');
        $ref->setAccessible(true);

        return $ref->invoke($tag);
    }
}
