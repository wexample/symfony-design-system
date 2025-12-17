<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Rendering;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Rendering\Asset;

class AssetTest extends TestCase
{
    public function testBuildViewNormalization(): void
    {
        $asset = new Asset('build/bundle/css/view.css', 'bundle/view', 'default', Asset::CONTEXT_LAYOUT);

        $ref = new \ReflectionMethod($asset, 'buildView');
        $ref->setAccessible(true);

        $result = $ref->invoke($asset, 'build/bundle/css/view.css');

        $this->assertSame('bundle/view', $result);
    }
}
