<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Service\NavigationService;

/**
 * When a view of a section is the page being read.
 */
class NavigationServiceTest extends TestCase
{
    public function testLitOnItsOwnRouteAndParameters(): void
    {
        $item = ['route' => 'accounting_show', 'params' => ['id' => 'a1']];

        $this->assertTrue(NavigationService::isCurrent($item, 'accounting_show', ['id' => 'a1']));
        $this->assertFalse(NavigationService::isCurrent($item, 'accounting_show', ['id' => 'b2']));
    }

    public function testLitOnTheFamilyOfPagesItMatches(): void
    {
        $item = ['route' => 'accounting_bank_index', 'params' => ['ledger' => 'a1'], 'match' => 'accounting_bank_'];

        $this->assertTrue(NavigationService::isCurrent($item, 'accounting_bank_show', ['id' => 'x9']));
    }

    public function testSeveralFamiliesMayBeMatched(): void
    {
        $item = ['route' => 'accounting_documents_index', 'match' => ['accounting_documents_', 'accounting_invoice_']];

        $this->assertTrue(NavigationService::isCurrent($item, 'accounting_invoice_edit', []));
        $this->assertFalse(NavigationService::isCurrent($item, 'accounting_bank_index', []));
    }

    public function testNothingIsLitOutsideARequest(): void
    {
        $this->assertFalse(NavigationService::isCurrent(['route' => 'home_index'], null, []));
    }

    public function testTabsGoDownToTheLevelThePageStandsIn(): void
    {
        $level = NavigationService::currentLevel([
            'label' => 'Acme',
            'href' => '/organizations/12',
            'back' => ['href' => '/organizations', 'label' => 'Organizations'],
            'actions' => null,
            'items' => [
                ['label' => 'Overview', 'href' => '/organizations/12', 'active' => false, 'children' => []],
                ['label' => 'Accounting', 'href' => '/accounting/7', 'active' => true, 'children' => [
                    ['label' => 'Documents', 'href' => '/accounting/7/documents', 'active' => true, 'children' => []],
                    ['label' => 'Bank', 'href' => '/accounting/7/bank', 'active' => false, 'children' => []],
                ]],
            ],
        ]);

        $this->assertSame(['Documents', 'Bank'], array_column($level['items'], 'label'));
        $this->assertSame(['href' => '/organizations/12', 'label' => 'Acme'], $level['back']);
    }

    public function testTabsStayOnALevelWhoseChildrenDoNotLeadToThePage(): void
    {
        $navigation = [
            'label' => null,
            'href' => null,
            'back' => null,
            'actions' => null,
            'items' => [
                ['label' => 'Overview', 'href' => '/', 'active' => true, 'children' => []],
                ['label' => 'Accounting', 'href' => '/accounting', 'active' => false, 'children' => [
                    ['label' => 'Bank', 'href' => '/accounting/bank', 'active' => false, 'children' => []],
                ]],
            ],
        ];

        $this->assertSame($navigation, NavigationService::currentLevel($navigation));
    }
}
