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

    public function testALinkOpensNoZoneAndIsNeverOpen(): void
    {
        $tools = ToolDockExtension::arrangeTools('demo', [
            ['name' => 'settings', 'group' => 'end', 'href' => '/settings', 'target' => 'panel', 'open' => true],
            ['name' => 'history', 'group' => 'end'],
        ], ['settings' => true]);

        $this->assertSame(['/settings', null], array_column($tools, 'href'));
        $this->assertSame(['panel', null], array_column($tools, 'target'));
        // Neither the page nor a state left behind opens a link.
        $this->assertSame([false, false], array_column($tools, 'open'));
    }

    public function testAnUnknownGroupIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ToolDockExtension::arrangeTools('demo', [['name' => 'detail', 'group' => 'left']]);
    }

    public function testSeparatorsAndControlsStandBetweenTheTools(): void
    {
        $items = ToolDockExtension::arrangeTools('demo', [
            ['name' => 'job'],
            ['type' => 'separator'],
            ['name' => 'session', 'group' => 'end', 'open' => true],
            ['type' => 'control', 'group' => 'end', 'for' => 'session', 'content' => '<input>'],
        ]);

        $this->assertSame(['tool', 'separator', 'tool', 'control'], array_column($items, 'type'));
        $this->assertSame('<input>', $items[3]['content']);
        $this->assertSame('tool-dock-demo-session', $items[3]['for_zone']);
    }

    public function testAControlShowsWithTheZoneItBelongsTo(): void
    {
        $control = ['type' => 'control', 'group' => 'end', 'for' => 'session', 'content' => '<input>'];

        $open = ToolDockExtension::arrangeTools('demo', [['name' => 'session', 'group' => 'end', 'open' => true], $control]);
        $closed = ToolDockExtension::arrangeTools('demo', [['name' => 'session', 'group' => 'end'], $control]);
        $free = ToolDockExtension::arrangeTools('demo', [['type' => 'control', 'content' => '<input>']]);

        $this->assertTrue($open[1]['shown']);
        $this->assertFalse($closed[1]['shown']);
        // Owned by no zone, always there.
        $this->assertTrue($free[0]['shown']);
    }

    public function testAnUnknownTypeIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ToolDockExtension::arrangeTools('demo', [['type' => 'slider']]);
    }
}
