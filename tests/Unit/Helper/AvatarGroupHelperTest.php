<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Helper\AvatarGroupHelper;

/** The same cases as tests/js/AvatarGroupHelper.test.ts. */
class AvatarGroupHelperTest extends TestCase
{
    public function testTheFirstFacesAndTheCountOfTheRest(): void
    {
        $this->assertSame(['shown' => ['a', 'b', 'c'], 'more' => 0], AvatarGroupHelper::split(['a', 'b', 'c'], 4));
        $this->assertSame(['shown' => ['a', 'b', 'c', 'd'], 'more' => 0], AvatarGroupHelper::split(['a', 'b', 'c', 'd'], 4));
        $this->assertSame(['shown' => ['a', 'b', 'c'], 'more' => 3], AvatarGroupHelper::split(['a', 'b', 'c', 'd', 'e', 'f'], 4));
        $this->assertSame(['shown' => [], 'more' => 2], AvatarGroupHelper::split(['a', 'b'], 1));
    }

    public function testTheInitialsOfAFaceWithNoPicture(): void
    {
        $this->assertSame('RW', AvatarGroupHelper::initials('Romain Weeger'));
        $this->assertSame('MA', AvatarGroupHelper::initials('maya'));
        $this->assertSame('ÉL', AvatarGroupHelper::initials('élodie Lambert'));
        $this->assertSame('', AvatarGroupHelper::initials('  '));
    }
}
