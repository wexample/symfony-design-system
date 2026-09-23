<?php

namespace Wexample\SymfonyDesignSystem\Service;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Table\Table;
use Twig\Environment as TwigEnvironment;
use Wexample\SymfonyDesignSystem\Class\Markdown\DataTableRenderer;
use Wexample\SymfonyTemplate\Enum\MarkdownFlavor;
use Wexample\SymfonyLoader\Rendering\RenderPass;
use Wexample\SymfonyTemplate\Service\MarkdownService;

/**
 * Markdown rendered into the design system: the same conversion as
 * `MarkdownService`, with its tables drawn by `data_table()`.
 *
 * A separate service and not a replacement: what goes into a page of an app can
 * carry the design system, what goes into a mail cannot, and the caller is the
 * one who knows which it is writing. Asking for this class is choosing the
 * first.
 *
 * The tables need the render pass of the page they land in, so this is meant to
 * be called while that page is drawn — the `markdown_ds` twig filter does it.
 * Called without one, it renders what `MarkdownService` renders.
 */
class DesignSystemMarkdownService extends MarkdownService
{
    /**
     * Handed to every table as `data_table()` options. A header that stays in
     * view is what a long table of a procedure needs, and the sticky frame
     * is the one the component already has.
     */
    private const array TABLE_OPTIONS = [
        'sticky' => true,
    ];

    private readonly DataTableRenderer $tableRenderer;

    public function __construct(TwigEnvironment $twig)
    {
        $this->tableRenderer = new DataTableRenderer($twig, self::TABLE_OPTIONS);
    }

    public function toHtml(
        string $markdown,
        MarkdownFlavor|string|null $flavor = null,
        ?RenderPass $renderPass = null
    ): string {
        // One renderer for every conversion, told which page it draws into for
        // the time of this one.
        $this->tableRenderer->setRenderPass($renderPass);

        try {
            return parent::toHtml($markdown, $flavor);
        } finally {
            $this->tableRenderer->setRenderPass(null);
        }
    }

    protected function configureEnvironment(
        Environment $environment,
        MarkdownFlavor $flavor
    ): void {
        // Above the table extension's own renderer, which stays underneath as
        // what a table falls back to.
        $environment->addRenderer(
            Table::class,
            $this->tableRenderer,
            10
        );
    }
}
