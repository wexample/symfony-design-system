<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Service\UiStateService;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

/**
 * A guided tour of a page: `tour(id, steps, options)`, each step pointing at
 * an element of the page. It starts on its own for a visitor who has neither
 * finished nor dismissed it — what they did is kept in the ui state, under
 * `ui.tour.<id>` — and any element carrying `data-tour-start="<id>"` starts it
 * again. `tour_status(id)` says what the visitor did: `completed`,
 * `dismissed`, or null.
 */
class TourExtension extends AbstractTemplateExtension
{
    private const string KEY_PREFIX = 'ui.tour.';

    public function __construct(
        ComponentsExtension $componentsExtension,
        private readonly UiStateService $uiState,
    ) {
        parent::__construct($componentsExtension);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'tour',
                /**
                 * @param array<array{target?: string, title?: string, body?: string, placement?: string}> $steps
                 * @param array{auto?: bool} $options
                 */
                function (Environment $twig, $context, string $id, array $steps, array $options = []): string {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/tour',
                        ['id' => $id],
                        [
                            'id' => $id,
                            'steps' => array_values($steps),
                            'auto' => $options['auto'] ?? true,
                            'status' => $this->tourStatus($id),
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
            new TwigFunction('tour_status', $this->tourStatus(...)),
        ];
    }

    public function tourStatus(string $id): ?string
    {
        $status = $this->uiState->get(self::KEY_PREFIX.$id);

        return is_string($status) ? $status : null;
    }
}
