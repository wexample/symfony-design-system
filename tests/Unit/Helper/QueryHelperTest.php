<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Helper\QueryHelper;

/**
 * A table's part of the query: the whole query without a key, the entry of its
 * key with one — and another table's part never touched.
 */
class QueryHelperTest extends TestCase
{
    public function testScoped(): void
    {
        $query = ['sort' => 'name', 'patients' => ['sort' => 'last']];

        $this->assertSame($query, QueryHelper::scoped($query));
        $this->assertSame(['sort' => 'last'], QueryHelper::scoped($query, 'patients'));
        $this->assertSame([], QueryHelper::scoped($query, 'users'));
        $this->assertSame([], QueryHelper::scoped(['users' => 'flat'], 'users'));
    }

    public function testUrlWithoutKeyWritesAtTheTop(): void
    {
        $this->assertSame(
            '/list?'.http_build_query(['owner' => 'a', 'sort' => 'name']),
            QueryHelper::url('/list', ['owner' => 'a', 'page' => 2], ['sort' => 'name'])
        );
        $this->assertSame('/list', QueryHelper::url('/list', ['sort' => 'name', 'page' => 2], ['sort' => null]));
    }

    public function testUrlWithKeyLeavesTheOtherTablesAlone(): void
    {
        $query = [
            'patients' => ['sort' => 'last', 'page' => 3],
            'users' => ['sort' => 'email', 'page' => 2],
            'tab' => 'list',
        ];

        $this->assertSame(
            '/list?'.http_build_query([
                'patients' => ['sort' => 'first'],
                'users' => ['sort' => 'email', 'page' => 2],
                'tab' => 'list',
            ]),
            QueryHelper::url('/list', $query, ['sort' => 'first'], 'patients')
        );
        // Emptied, the table's key goes from the address.
        $this->assertSame(
            '/list?'.http_build_query(['users' => ['sort' => 'email', 'page' => 2], 'tab' => 'list']),
            QueryHelper::url('/list', $query, ['sort' => null], 'patients')
        );
    }
}
