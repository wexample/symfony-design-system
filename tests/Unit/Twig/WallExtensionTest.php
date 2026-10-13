<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Twig\WallExtension;

/**
 * The rows a tile spans, a twelfth of a column each — what the vue twin
 * computes with WallHelper.wallSpan().
 */
class WallExtensionTest extends TestCase
{
    public function testAPictureSpansAsManyTwelfthsAsItsHeightTakes(): void
    {
        $this->assertSame(12, WallExtension::span(1200, 1200));
        $this->assertSame(9, WallExtension::span(1600, 1200));
        $this->assertSame(16, WallExtension::span(1200, 1600));
    }

    public function testASliverOrATowerIsHeldBetweenHalfAndTwiceItsWidth(): void
    {
        $this->assertSame(6, WallExtension::span(4000, 500));
        $this->assertSame(24, WallExtension::span(500, 4000));
    }

    public function testAPictureWhoseSizeIsNotKnownStandsSquare(): void
    {
        $this->assertSame(12, WallExtension::span(null, null));
        $this->assertSame(12, WallExtension::span(800, 0));
    }
}
