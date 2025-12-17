<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Rendering;

use PHPUnit\Framework\TestCase;
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

    private function invokeGetDestinationPath(JsAssetTag $tag): ?string
    {
        $ref = new \ReflectionMethod($tag, 'getDestinationPath');
        $ref->setAccessible(true);

        return $ref->invoke($tag);
    }
}
