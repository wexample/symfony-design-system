<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Class;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Class\UploadRules;

/**
 * What an upload address takes, read as the HTML `accept` attribute is: one
 * entry matching is enough, and nothing said is anything.
 */
class UploadRulesTest extends TestCase
{
    public function testNothingSaidTakesAnything(): void
    {
        $rules = new UploadRules();

        $this->assertTrue($rules->allowsName('anything.exe'));
        $this->assertTrue($rules->allowsSize(PHP_INT_MAX));
    }

    public function testAnExtensionDecidesOnTheName(): void
    {
        $rules = new UploadRules(['.xlsx', '.csv']);

        $this->assertTrue($rules->allowsName('Planning W29.XLSX'));
        $this->assertFalse($rules->allowsName('notes.pdf'));
    }

    public function testAMimeTypeLeavesTheNameToTheContent(): void
    {
        $rules = new UploadRules(['.xlsx', 'image/*']);

        $this->assertTrue($rules->allowsName('sheet.xlsx'));
        $this->assertNull($rules->allowsName('photo.heic'));
        $this->assertTrue($rules->allowsMimeType('image/heic'));
        $this->assertFalse($rules->allowsMimeType('application/pdf'));
    }

    public function testTheSizeIsAMaximum(): void
    {
        $rules = new UploadRules([], 1024);

        $this->assertTrue($rules->allowsSize(1024));
        $this->assertFalse($rules->allowsSize(1025));
    }

    public function testSignedAndReadBack(): void
    {
        $rules = UploadRules::fromArray((new UploadRules(['.xlsx'], 2048))->toArray());

        $this->assertSame(['.xlsx'], $rules->accept);
        $this->assertSame(2048, $rules->maxSize);
        $this->assertSame([], (new UploadRules())->toArray());
    }
}
