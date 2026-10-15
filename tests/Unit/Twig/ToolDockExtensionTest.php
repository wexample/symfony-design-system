<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Twig;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Twig\ToolDockExtension;

/**
 * The tools of a dock as it draws them: the top of the bar first, each open as
 * the visitor left it or as the page wants it, one only open in a group.
 */
class ToolDockExtensionTest extends TestCase
{
    public function testTheTopOfTheBarComesFirst(): void
    {
        $tools = ToolDockExtension::arrangeTools('demo', [
            ['name' => 'detail', 'group' => 'end'],
            ['name' => 'outline'],
            ['name' => 'history', 'group' => 'end'],
        ]);

        $this->assertSame(['outline', 'detail', 'history'], array_column($tools, 'name'));
    }

    public function testTheVisitorsChoiceWinsOverThePages(): void
    {
        $tools = ToolDockExtension::arrangeTools('demo', [
            ['name' => 'detail', 'open' => true],
            ['name' => 'outline', 'group' => 'end'],
        ], ['detail' => false, 'outline' => true]);

        $this->assertSame([false, true], array_column($tools, 'open'));
    }

    public function testOneOpenAtATimeInAGroup(): void
    {
        $tools = ToolDockExtension::arrangeTools('demo', [
            ['name' => 'detail', 'group' => 'end', 'open' => true],
            ['name' => 'history', 'group' => 'end', 'open' => true],
            ['name' => 'outline', 'open' => true],
        ]);

        $this->assertSame(
            ['outline' => true, 'detail' => true, 'history' => false],
            array_combine(array_column($tools, 'name'), array_column($tools, 'open'))
        );
    }

    public function testWhatTheVisitorOpenedWinsOverWhatThePageOpens(): void
    {
        $tools = ToolDockExtension::arrangeTools('demo', [
            ['name' => 'detail', 'group' => 'end', 'open' => true],
            ['name' => 'history', 'group' => 'end'],
        ], ['history' => true]);

        $this->assertSame([false, true], array_column($tools, 'open'));
    }

    public function testEachToolHasItsZoneAndItsKey(): void
    {
        $tool = ToolDockExtension::arrangeTools('board.file', [['name' => 'detail']])[0];

        $this->assertSame('tool-dock-board-file-detail', $tool['zone']);
        $this->assertSame('ui.layout.dock.board.file.detail', $tool['state']);
    }

    public function testAnUnknownGroupIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ToolDockExtension::arrangeTools('demo', [['name' => 'detail', 'group' => 'left']]);
    }
}
