<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Wexample\SymfonyLoader\Rendering\RenderPass;

/**
 * Where the page being drawn is shown: on a page of its own, or held by
 * something else — a modal, a panel, an embed. A block meant for one of the two
 * is wrapped in `{% if page_is_held() %}` or its negation, and is not drawn at
 * all where it does not belong.
 */
class PageContextExtension extends AbstractExtension
{
    private const array HELD_BASES = [
        RenderPass::BASE_MODAL,
        RenderPass::BASE_PANEL,
        RenderPass::BASE_EMBED,
    ];

    public function getFunctions(): array
    {
        return [
            new TwigFunction('page_is_held', $this->pageIsHeld(...), ['needs_context' => true]),
        ];
    }

    public function pageIsHeld(array $context): bool
    {
        $renderPass = $context['render_pass'] ?? null;

        return $renderPass instanceof RenderPass
            && in_array($renderPass->getLayoutBase(), self::HELD_BASES, true);
    }
}
