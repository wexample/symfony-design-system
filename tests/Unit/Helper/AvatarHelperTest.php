<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Helper\AvatarHelper;

/**
 * What an avatar engine reads from its seed: the same each time, and spread
 * over what there is to pick.
 */
class AvatarHelperTest extends TestCase
{
    public function testTheSameSeedPicksTheSame(): void
    {
        $this->assertSame(AvatarHelper::avatarPick('42', 'eyes', 26), AvatarHelper::avatarPick('42', 'eyes', 26));
        $this->assertSame(AvatarHelper::avatarColor('42'), AvatarHelper::avatarColor('42'));
    }

    public function testEachChoiceReadsTheSeedOnItsOwn(): void
    {
        $picks = array_map(fn (int $seed) => [AvatarHelper::avatarPick((string) $seed, 'eyes', 26), AvatarHelper::avatarPick((string) $seed, 'hue', 26)], range(1, 200));

        $this->assertGreaterThan(20, count(array_unique(array_column($picks, 0))));
        $this->assertNotSame(array_column($picks, 0), array_column($picks, 1));
    }

    public function testInitials(): void
    {
        $this->assertSame('RW', AvatarHelper::avatarInitials('Romain Weeger'));
        $this->assertSame('ÉL', AvatarHelper::avatarInitials('élodie lambert'));
        $this->assertSame('JE', AvatarHelper::avatarInitials('jean'));
        $this->assertSame('', AvatarHelper::avatarInitials('  '));
    }
}
