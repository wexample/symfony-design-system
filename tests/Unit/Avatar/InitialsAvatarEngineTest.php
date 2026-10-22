<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Avatar;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Avatar\InitialsAvatarEngine;

class InitialsAvatarEngineTest extends TestCase
{
    public function testTheInitialsOfTheNameOnTheColourOfTheSeed(): void
    {
        $engine = new InitialsAvatarEngine();
        $face = $engine->render('42', 'Ada <Lovelace>');

        $this->assertStringContainsString('>AL</text>', $face);
        $this->assertSame($face, $engine->render('42', 'Ada <Lovelace>'));
        $this->assertNotSame($face, $engine->render('43', 'Ada <Lovelace>'));
        $this->assertNotFalse(simplexml_load_string($face));
    }
}
