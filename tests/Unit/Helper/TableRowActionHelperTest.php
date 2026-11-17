<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Helper\TableRowActionHelper;

/**
 * The actions a server hands to a table row: the same three written once,
 * each carrying the `rel` the table orders them and links its title by.
 */
class TableRowActionHelperTest extends TestCase
{
    public function testOpenIsAnEyeLeadingWhereItIsGiven(): void
    {
        $this->assertSame(
            ['href' => '/p/1', 'icon' => 'ph:bold/eye', 'label' => 'Open', 'rel' => 'open'],
            TableRowActionHelper::open('/p/1'),
        );
        $this->assertSame('panel', TableRowActionHelper::open('/p/1', 'panel')['target']);
    }

    public function testEditIsAPencilInAPanel(): void
    {
        $this->assertSame(
            ['href' => '/p/1/edit', 'icon' => 'ph:bold/pencil-simple', 'label' => 'Edit', 'target' => 'panel', 'rel' => 'edit'],
            TableRowActionHelper::edit('/p/1/edit'),
        );
    }

    public function testDeleteIsPostedAndAsksFirst(): void
    {
        $this->assertSame(
            [
                'href' => '/p/1/delete',
                'method' => 'post',
                'token' => 'tok',
                'icon' => 'ph:bold/trash',
                'label' => 'Delete',
                'confirm' => ['title' => 'Delete P', 'message' => 'Gone.', 'accept' => 'Delete'],
                'rel' => 'delete',
            ],
            TableRowActionHelper::delete('/p/1/delete', 'tok', 'Delete P', 'Gone.'),
        );
    }

    public function testAnActionOfTheRowOwnCarriesNoRel(): void
    {
        $action = TableRowActionHelper::post('/p/1/check', 'tok', 'ph:bold/plugs-connected', 'Test');

        $this->assertArrayNotHasKey('rel', $action);
        $this->assertArrayNotHasKey('confirm', $action);
    }
}
