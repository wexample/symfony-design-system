<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * Items on coloured lanes: `graph_log(items)`, each item { id, parents, title,
 * href, refs, code, meta, date }. A git history is one — symfony-coding's
 * `git_graph_items()` gives it — but anything branching is.
 */
class GraphLogExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'graph_log',
                function (Environment $twig, $context, array $items, array $options = []): string {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/graph-log',
                        [
                            'items' => $items,
                            'class' => $options['class'] ?? null,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
