<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use InvalidArgumentException;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Service\UiStateService;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

/**
 * Tools along the edge of a page, as an IDE has them: a bar of icons, each
 * opening a zone beside the page's own, in a column split between the bar's
 * groups — what the top of the bar opens stands in the upper part of the
 * column, what its foot opens in the lower part, one open at a time in each.
 *
 * A page describes its tools and nothing else: the column, its zones, their
 * handles and the bar are drawn from that description, so where a tool stands
 * is a value — the one a visitor will move — and not markup a page wrote.
 * What each tool was left at comes back with the page.
 */
class ToolDockExtension extends AbstractTemplateExtension
{
    public const GROUP_START = 'start';
    public const GROUP_END = 'end';

    private const GROUPS = [self::GROUP_START, self::GROUP_END];

    private const STATE_PREFIX = 'ui.layout.dock.';

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
                'tool_dock',
                function (
                    Environment $twig,
                    $context,
                    string $id,
                    array $tools,
                    array $options = [],
                ) {
                    $states = [];

                    foreach ($tools as $tool) {
                        $name = $tool['name'] ?? null;

                        if (is_string($name)) {
                            $states[$name] = $this->uiState->get(self::stateKey($id, $name));
                        }
                    }

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/tool-dock',
                        [
                            'id' => $id,
                            'tools' => self::arrangeTools($id, $tools, $states),
                            'class' => $options['class'] ?? null,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }

    public static function stateKey(string $id, string $name): string
    {
        return self::STATE_PREFIX.$id.'.'.$name;
    }

    /**
     * The tools as the dock draws them: those of the top of the bar first, each
     * open as the visitor left it (`$states`, null when never touched) or as the
     * page wants it the first time (`open`), and one only open in a group — the
     * first, should the states say otherwise.
     *
     * A tool shows a page loaded the first time it opens (`url`), or what the
     * page drew for it (`content`).
     *
     * @param array<int, array<string, mixed>> $tools
     * @param array<string, bool|null>        $states
     *
     * @return array<int, array<string, mixed>>
     */
    public static function arrangeTools(string $id, array $tools, array $states = []): array
    {
        $arranged = array_fill_keys(self::GROUPS, []);
        $groupOpen = array_fill_keys(self::GROUPS, false);

        foreach ($tools as $tool) {
            $name = $tool['name'] ?? null;
            $group = $tool['group'] ?? self::GROUP_START;

            if (! is_string($name) || '' === $name) {
                throw new InvalidArgumentException('A tool of the dock "'.$id.'" has no name.');
            }

            if (! in_array($group, self::GROUPS, true)) {
                throw new InvalidArgumentException('The tool "'.$name.'" of the dock "'.$id.'" is in an unknown group "'.$group.'".');
            }

            $open = (bool) ($states[$name] ?? $tool['open'] ?? false);
            $open = $open && ! $groupOpen[$group];
            $groupOpen[$group] = $groupOpen[$group] || $open;

            $arranged[$group][] = [
                'name' => $name,
                'label' => $tool['label'] ?? $name,
                'icon' => $tool['icon'] ?? null,
                'group' => $group,
                'zone' => 'tool-dock-'.self::slug($id).'-'.self::slug($name),
                'state' => self::stateKey($id, $name),
                'open' => $open,
                'url' => $tool['url'] ?? null,
                'content' => $tool['content'] ?? null,
            ];
        }

        return array_merge(...array_values($arranged));
    }

    private static function slug(string $value): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($value)), '-');
    }
}
