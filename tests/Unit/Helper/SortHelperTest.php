<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Helper;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Helper\SortHelper;

/**
 * The order of the server table, which the vue table follows through
 * SortHelper.ts: tests/js/SortHelper.test.ts holds the same cases.
 */
class SortHelperTest extends TestCase
{
    private const array DEFAULT = ['key' => 'measured', 'direction' => 'desc'];

    public function testCycleOnAnotherColumnIsAscendingDescendingDefault(): void
    {
        $first = SortHelper::next(null, 'name', self::DEFAULT);
        $second = SortHelper::next($first, 'name', self::DEFAULT);
        $third = SortHelper::next($second, 'name', self::DEFAULT);

        $this->assertSame(['key' => 'name', 'direction' => 'asc'], $first);
        $this->assertSame(['key' => 'name', 'direction' => 'desc'], $second);
        $this->assertSame(self::DEFAULT, $third);
    }

    public function testDefaultColumnTurnsAndComesBack(): void
    {
        $turned = SortHelper::next(self::DEFAULT, 'measured', self::DEFAULT);

        $this->assertSame(['key' => 'measured', 'direction' => 'asc'], $turned);
        $this->assertSame(self::DEFAULT, SortHelper::next($turned, 'measured', self::DEFAULT));
    }

    public function testWithoutDefaultTheThirdPressGivesTheRowsOrderBack(): void
    {
        $this->assertNull(SortHelper::next(['key' => 'name', 'direction' => 'desc'], 'name'));
    }

    public function testAria(): void
    {
        $this->assertSame('descending', SortHelper::aria(self::DEFAULT, 'measured'));
        $this->assertSame('ascending', SortHelper::aria(['key' => 'name', 'direction' => 'asc'], 'name'));
        $this->assertSame('none', SortHelper::aria(self::DEFAULT, 'name'));
        $this->assertSame('none', SortHelper::aria(null, 'name'));
    }

    public function testFromQueryLetsOnlyAllowedKeysThrough(): void
    {
        $allowed = ['name', 'measured'];

        $this->assertSame(
            ['key' => 'name', 'direction' => 'desc'],
            SortHelper::fromQuery(['sort' => 'name', 'direction' => 'desc'], $allowed, self::DEFAULT)
        );
        $this->assertSame(
            ['key' => 'name', 'direction' => 'asc'],
            SortHelper::fromQuery(['sort' => 'name', 'direction' => 'sideways'], $allowed, self::DEFAULT)
        );
        $this->assertSame(self::DEFAULT, SortHelper::fromQuery(['sort' => 'password; DROP'], $allowed, self::DEFAULT));
        $this->assertSame(self::DEFAULT, SortHelper::fromQuery(['sort' => ['name']], $allowed, self::DEFAULT));
        $this->assertSame(self::DEFAULT, SortHelper::fromQuery([], $allowed, self::DEFAULT));
    }

    public function testEmptyValuesStayLastBothWays(): void
    {
        $rows = [
            ['name' => 'none', 'measured' => null],
            ['name' => 'old', 'measured' => '2026-01-01T10:00:00Z'],
            ['name' => 'blank', 'measured' => ''],
            ['name' => 'new', 'measured' => '2026-09-30T10:00:00Z'],
        ];

        $this->assertSame(
            ['old', 'new', 'none', 'blank'],
            array_column(SortHelper::apply($rows, ['key' => 'measured', 'direction' => 'asc']), 'name')
        );
        $this->assertSame(
            ['new', 'old', 'none', 'blank'],
            array_column(SortHelper::apply($rows, ['key' => 'measured', 'direction' => 'desc']), 'name')
        );
    }

    public function testWordsFollowTheLocale(): void
    {
        $rows = [['name' => 'Zoé'], ['name' => 'Élodie'], ['name' => 'adrien'], ['name' => 'Eric']];

        $this->assertSame(
            ['adrien', 'Élodie', 'Eric', 'Zoé'],
            array_column(SortHelper::apply($rows, ['key' => 'name', 'direction' => 'asc'], 'fr_FR'), 'name')
        );
    }

    public function testNumbersDatesAndFiguresInWords(): void
    {
        $collator = SortHelper::collator('en');

        $this->assertLessThan(0, SortHelper::compareValues(9, 10, $collator));
        $this->assertLessThan(0, SortHelper::compareValues('Room 9', 'Room 10', $collator));
        $this->assertLessThan(0, SortHelper::compareValues(
            new DateTimeImmutable('2026-01-01'),
            new DateTimeImmutable('2026-02-01'),
            $collator
        ));
    }

    public function testDottedKeyAndGroupsSortedAmongThemselves(): void
    {
        $rows = [
            ['group' => 'First'],
            ['person' => ['last' => 'Martin']],
            ['person' => ['last' => 'Durand']],
            ['group' => 'Second'],
            ['person' => ['last' => 'Bernard']],
            ['person' => ['last' => 'Adam']],
        ];

        $sorted = SortHelper::apply($rows, ['key' => 'person.last', 'direction' => 'asc'], 'fr');

        $this->assertSame(
            ['First', 'Durand', 'Martin', 'Second', 'Adam', 'Bernard'],
            array_map(fn (array $row) => $row['group'] ?? $row['person']['last'], $sorted)
        );
    }

    public function testEqualRowsKeepTheirOrder(): void
    {
        $rows = [['id' => 1, 'v' => 'a'], ['id' => 2, 'v' => 'a'], ['id' => 3, 'v' => 'a']];

        $this->assertSame([1, 2, 3], array_column(SortHelper::apply($rows, ['key' => 'v', 'direction' => 'desc']), 'id'));
    }

    public function testAllowedKeysAreTheSortableColumnsAndTheDefault(): void
    {
        $columns = [
            ['key' => 'name', 'sortable' => true, 'sort_key' => 'last_name'],
            ['key' => 'measured', 'sortable' => false, 'sort_key' => 'measured'],
            ['key' => 'actions', 'sort_key' => 'actions'],
        ];

        $this->assertSame(['last_name', 'measured'], SortHelper::allowedKeys($columns, self::DEFAULT));
        $this->assertSame(['last_name'], SortHelper::allowedKeys($columns));
    }

    public function testUrlCarriesTheNextOrderKeepsFiltersAndDropsThePage(): void
    {
        $query = ['owner' => ['a', 'b'], 'page' => 3, 'sort' => 'name', 'direction' => 'asc'];

        $this->assertSame(
            '/list?'.http_build_query(['owner' => ['a', 'b'], 'sort' => 'name', 'direction' => 'desc']),
            SortHelper::url('/list', $query, ['key' => 'name', 'direction' => 'desc'], self::DEFAULT)
        );
        // Back to the default: nothing of the order left in the address.
        $this->assertSame(
            '/list?'.http_build_query(['owner' => ['a', 'b']]),
            SortHelper::url('/list', $query, self::DEFAULT, self::DEFAULT)
        );
        $this->assertSame('/list', SortHelper::url('/list', ['page' => 2, 'sort' => 'name'], null));
    }
}
