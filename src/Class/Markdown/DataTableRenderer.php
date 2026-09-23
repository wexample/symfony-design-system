<?php

namespace Wexample\SymfonyDesignSystem\Class\Markdown;

use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Extension\Table\TableRenderer;
use League\CommonMark\Extension\Table\TableRow;
use League\CommonMark\Extension\Table\TableSection;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Throwable;
use Twig\Environment;
use Twig\Markup;
use Wexample\SymfonyLoader\Rendering\RenderPass;

/**
 * A markdown table drawn by the design system's `data_table()`.
 *
 * The data is read off the parsed table and not off its html: the header row
 * gives the columns and their alignment, each body row gives one row, and every
 * cell is rendered inline first — a cell of markdown holds bold, links and
 * code, so it goes to the table as html and not as text to escape.
 *
 * A component is drawn inside a page's render pass, which is what registers
 * its styles and scripts with the page. Without one — a conversion made outside
 * any page — and the moment the table does not read as columns and rows, the
 * plain `<table>` of CommonMark comes out instead, and the content stylesheet
 * dresses it. A document never loses a table to this renderer.
 */
final class DataTableRenderer implements NodeRendererInterface
{
    private const string TABLE_FUNCTION = 'data_table';

    private const string KEY_PREFIX = 'c';

    private readonly TableRenderer $fallback;

    /**
     * The pass of the page being drawn, for the time of one conversion.
     */
    private ?RenderPass $renderPass = null;

    /**
     * @param array<string, mixed> $options handed to `data_table()` as they are
     */
    public function __construct(
        private readonly Environment $twig,
        private readonly array $options = [],
    ) {
        $this->fallback = new TableRenderer();
    }

    public function setRenderPass(?RenderPass $renderPass): void
    {
        $this->renderPass = $renderPass;
    }

    public function render(
        Node $node,
        ChildNodeRendererInterface $childRenderer
    ): \Stringable|string|null {
        if (null === $this->renderPass) {
            return $this->fallback->render($node, $childRenderer);
        }

        try {
            $table = $this->read($node, $childRenderer);
            $function = $this->twig->getFunction(self::TABLE_FUNCTION);

            if (null !== $table && null !== $function) {
                return (string) ($function->getCallable())(
                    $this->twig,
                    ['render_pass' => $this->renderPass],
                    $table['columns'],
                    $table['rows'],
                    $this->options,
                );
            }
        } catch (Throwable) {
            // Whatever went wrong, the document still carries its table.
        }

        return $this->fallback->render($node, $childRenderer);
    }

    /**
     * @return array{columns: array<int, array<string, mixed>>, rows: array<int, array<string, Markup>>}|null
     */
    private function read(
        Node $node,
        ChildNodeRendererInterface $childRenderer
    ): ?array {
        if (! $node instanceof Table) {
            return null;
        }

        $head = null;
        $body = [];

        foreach ($node->children() as $section) {
            if (! $section instanceof TableSection) {
                return null;
            }

            foreach ($section->children() as $row) {
                if (! $row instanceof TableRow) {
                    return null;
                }

                $cells = [];

                foreach ($row->children() as $cell) {
                    if (! $cell instanceof TableCell) {
                        return null;
                    }

                    $cells[] = $cell;
                }

                if ($section->isHead()) {
                    // One header row is what a pipe table has; a second is a
                    // shape `data_table()` has no place for.
                    if (null !== $head) {
                        return null;
                    }

                    $head = $cells;
                } else {
                    $body[] = $cells;
                }
            }
        }

        if (null === $head || [] === $head) {
            return null;
        }

        $columns = [];

        foreach ($head as $index => $cell) {
            $columns[] = array_filter([
                'key' => self::KEY_PREFIX.$index,
                'label' => $this->inline($cell, $childRenderer),
                'cell' => 'html',
                'align' => $cell->getAlign(),
            ], static fn (mixed $value): bool => null !== $value);
        }

        $rows = [];

        foreach ($body as $cells) {
            // A body row wider than the header has cells without a column.
            if (count($cells) > count($head)) {
                return null;
            }

            $row = [];

            foreach ($cells as $index => $cell) {
                $row[self::KEY_PREFIX.$index] = $this->inline($cell, $childRenderer);
            }

            $rows[] = $row;
        }

        return ['columns' => $columns, 'rows' => $rows];
    }

    private function inline(
        TableCell $cell,
        ChildNodeRendererInterface $childRenderer
    ): Markup {
        // Already rendered and already escaped by CommonMark: marked as such so
        // that twig prints it rather than escaping it a second time.
        return new Markup($childRenderer->renderNodes($cell->children()), 'UTF-8');
    }
}
