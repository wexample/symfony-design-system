<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Twig\PaginationExtension;

/**
 * The numbers a pager shows: all of them while they fit, otherwise the first,
 * the last, the current one and its neighbours, an ellipsis (null) for each
 * gap — what the vue twin computes, counted from 1.
 */
class PaginationExtensionTest extends TestCase
{
    public function testEveryPageWhileTheyFit(): void
    {
        $this->assertSame([1, 2, 3, 4, 5, 6, 7], PaginationExtension::visiblePages(4, 7));
    }

    public function testAGapOnTheFarSide(): void
    {
        $this->assertSame([1, 2, 3, 4, null, 20], PaginationExtension::visiblePages(2, 20));
    }

    public function testAGapOnEachSide(): void
    {
        $this->assertSame([1, null, 8, 9, 10, 11, 12, null, 20], PaginationExtension::visiblePages(10, 20));
    }

    public function testTheLastPage(): void
    {
        $this->assertSame([1, null, 18, 19, 20], PaginationExtension::visiblePages(20, 20));
    }

    public function testNeverFewerThanFiveSlots(): void
    {
        $this->assertSame([1, null, 9, 10, 11, null, 20], PaginationExtension::visiblePages(10, 20, 3));
    }
}
