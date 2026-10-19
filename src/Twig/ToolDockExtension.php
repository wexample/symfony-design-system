<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use InvalidArgumentException;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;
use Wexample\SymfonyPreference\Service\PreferenceService;

/**
 * Tools as an IDE has them: a bar of icons, each opening a zone, the zones
 * split between the bar's two groups — one open at a time in each. Standing
 * on its end (`vertical`), the bar is at the end of the page's row and opens a
 * column beside it, what its top opens over what its foot opens. Lying along
 * the top (`horizontal`), it is the whole body of the page: what its start
 * opens stands beside what its end opens, under it.
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

    private const ORIENTATIONS = ['vertical', 'horizontal'];

    private const STATE_PREFIX = 'ui.layout.dock.';

    public function __construct(
        ComponentsExtension $componentsExtension,
        private readonly PreferenceService $preferences,
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
                    $orientation = $options['orientation'] ?? 'vertical';

                    if (! in_array($orientation, self::ORIENTATIONS, true)) {
                        throw new InvalidArgumentException('The dock "'.$id.'" has an unknown orientation "'.$orientation.'".');
                    }

                    $states = [];

                    foreach ($tools as $tool) {
                        $name = $tool['name'] ?? null;

                        if (is_string($name)) {
                            $states[$name] = $this->preferences->get(self::stateKey($id, $name));
                        }
                    }

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/tool-dock',
                        [
                            'id' => $id,
                            'tools' => self::arrangeTools($id, $tools, $states),
                            'orientation' => $orientation,
                            // A bar a notch smaller, part of a panel rather
                            // than of the page (zone--toolbar--compact).
                            'compact' => (bool) ($options['compact'] ?? false),
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
     * page wants it the first time (`open`), and one only open in a group: one
     * the visitor opened before one the page opens by default, the first of
     * them should the states say otherwise.
     *
     * A tool shows a page loaded the first time it opens (`url`), or what the
     * page drew for it (`content`) — edge to edge and taking the zone's height
     * when it is sized by the room rather than by what it holds (`fill`): an
     * editor.
     *
     * An icon given an `href` opens no zone: it is a link of the bar, opening
     * its page where its `target` says — `panel`, `modal`, `dock` (as
     * button_target does), `_blank` for another window, or in place when
     * nothing is said. The settings, a page read and closed.
     *
     * @param array<int, array<string, mixed>> $tools
     * @param array<string, bool|null>        $states
     *
     * @return array<int, array<string, mixed>>
     */
    public static function arrangeTools(string $id, array $tools, array $states = []): array
    {
        $arranged = array_fill_keys(self::GROUPS, []);
        // The tool a group opens: the first the visitor opened, or failing
        // that the first the page opens and the visitor did not close.
        $chosen = array_fill_keys(self::GROUPS, null);
        $byDefault = array_fill_keys(self::GROUPS, null);

        foreach ($tools as $tool) {
            $name = $tool['name'] ?? null;
            $group = $tool['group'] ?? self::GROUP_START;

            if (! is_string($name) || '' === $name) {
                throw new InvalidArgumentException('A tool of the dock "'.$id.'" has no name.');
            }

            if (! in_array($group, self::GROUPS, true)) {
                throw new InvalidArgumentException('The tool "'.$name.'" of the dock "'.$id.'" is in an unknown group "'.$group.'".');
            }

            $href = $tool['href'] ?? null;
            $state = null === $href ? ($states[$name] ?? null) : null;

            if (true === $state) {
                $chosen[$group] ??= $name;
            } elseif (null === $state && null === $href && ($tool['open'] ?? false)) {
                $byDefault[$group] ??= $name;
            }

            $arranged[$group][] = [
                'name' => $name,
                'label' => $tool['label'] ?? $name,
                'icon' => $tool['icon'] ?? null,
                'group' => $group,
                'zone' => 'tool-dock-'.self::slug($id).'-'.self::slug($name),
                'state' => self::stateKey($id, $name),
                'open' => false,
                'url' => $tool['url'] ?? null,
                'content' => $tool['content'] ?? null,
                'fill' => (bool) ($tool['fill'] ?? false),
                'href' => $href,
                'target' => $tool['target'] ?? null,
            ];
        }

        foreach ($arranged as $group => &$groupTools) {
            $open = $chosen[$group] ?? $byDefault[$group];

            foreach ($groupTools as &$tool) {
                $tool['open'] = $tool['name'] === $open;
            }
        }

        return array_merge(...array_values($arranged));
    }

    private static function slug(string $value): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($value)), '-');
    }
}
