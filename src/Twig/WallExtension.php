<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * Pictures hung on a wall, drawn by the server: `wall(items, options)`, each
 * item as media() takes it — { src, alt, width, height, srcset, sizes, href,
 * caption } —, `class` the only option. The tiles of a wall are as tall as
 * their ratio says: span() is what WallHelper.wallSpan() computes for the
 * vue collection.
 */
class WallExtension extends AbstractTemplateExtension
{
    /**
     * A row is this fraction of a column's width (css/shapes/_wall.scss).
     */
    public const int ROWS_PER_COLUMN = 12;

    /**
     * Past these ratios, the picture is cropped rather than drawn as a
     * sliver or a tower.
     */
    public const float RATIO_MIN = 0.5;

    public const float RATIO_MAX = 2;

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'wall',
                function (Environment $twig, $context, iterable $items, array $options = []): string {
                    $tiles = [];

                    foreach ($items as $item) {
                        $tiles[] = $item + ['span' => self::span($item['width'] ?? null, $item['height'] ?? null)];
                    }

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/wall',
                        [
                            'items' => $tiles,
                            'class' => $options['class'] ?? null,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }

    /**
     * The rows a picture of width × height spans; square when its size is
     * not known.
     */
    public static function span(
        ?int $width,
        ?int $height
    ): int {
        $ratio = $width && $height ? $height / $width : 1;

        return (int) round(max(self::RATIO_MIN, min(self::RATIO_MAX, $ratio)) * self::ROWS_PER_COLUMN);
    }
}
