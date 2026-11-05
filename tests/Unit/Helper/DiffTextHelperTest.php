<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Helper\DiffTextHelper;

// The same cases as tests/js/DiffTextHelper.test.ts: the two faces agree.
class DiffTextHelperTest extends TestCase
{
    private static function shape(array $lines): array
    {
        return array_map(static fn (array $line): string => DiffTextHelper::FOLD === $line['type']
            ? '~'.$line['count']
            : $line['type'][0].($line['old'] ?? '-').':'.($line['new'] ?? '-').' '.$line['text'], $lines);
    }

    public function testLinesKeptRemovedAndAddedEachWithItsNumbers(): void
    {
        $this->assertSame(['s1:1 a', 'r2:- b', 'a-:2 B', 's3:3 c', 'a-:4 d'], self::shape(DiffTextHelper::lines("a\nb\nc", "a\nB\nc\nd")));
    }

    public function testNothingBeforeIsEveryLineAddedNothingAfterEveryLineRemoved(): void
    {
        $this->assertSame(['a-:1 x', 'a-:2 y'], self::shape(DiffTextHelper::lines('', "x\ny")));
        $this->assertSame(['r1:- x'], self::shape(DiffTextHelper::lines('x', '')));
        $this->assertSame(['same'], array_column(DiffTextHelper::lines('same', 'same'), 'type'));
    }

    public function testALineChangedIsGivenItsWordsTheOnesChangedMarked(): void
    {
        [$removed, $added] = DiffTextHelper::lines('Hello {{ name }}!', 'Hello {{ user.name }}!');
        $this->assertSame([['text' => 'Hello {{ name }}!', 'changed' => false]], $removed['parts']);
        $this->assertSame([['text' => 'Hello {{ ', 'changed' => false], ['text' => 'user.', 'changed' => true], ['text' => 'name }}!', 'changed' => false]], $added['parts']);

        [$was, $is] = DiffTextHelper::lines('Status: draft', 'Status: published');
        $this->assertSame([['text' => 'Status: ', 'changed' => false], ['text' => 'draft', 'changed' => true]], $was['parts']);
        $this->assertSame([['text' => 'Status: ', 'changed' => false], ['text' => 'published', 'changed' => true]], $is['parts']);
    }

    public function testTheLinesKeptFarFromAnyChangeAreFolded(): void
    {
        $before = implode("\n", array_map(static fn (int $i): string => 'line '.$i, range(1, 12)));
        $after = str_replace('line 6', 'line six', $before);
        $rows = DiffTextHelper::rows($before, $after, 1);

        $this->assertSame(['~4', 's5:5 line 5', 'r6:- line 6', 'a-:6 line six', 's7:7 line 7', '~5'], self::shape($rows));
        $this->assertSame(['added' => 1, 'removed' => 1], DiffTextHelper::stats($rows));
    }

    public function testSideBySideARemovalFacesTheAdditionThatReplacedIt(): void
    {
        $pairs = DiffTextHelper::sideBySide(DiffTextHelper::rows("a\nb\nc", "a\nB\nX\nc", -1));

        $this->assertSame(['a|a', 'b|B', '_|X', 'c|c'], array_map(static fn (array $pair): string => ($pair['left']['text'] ?? '_').'|'.($pair['right']['text'] ?? '_'), $pairs));
    }
}
