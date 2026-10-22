<?php

namespace Wexample\SymfonyDesignSystem\Interface;

/**
 * Declares that a bundle ships elements of the design system.
 *
 * A bundle publishing front assets to the loader is not thereby a source of
 * elements: a page, a stylesheet of its own and a hundred images all travel the
 * same way. So saying so is a separate sentence, and the registry only ever
 * holds what a bundle signed for.
 */
interface DesignSystemElementsBundleInterface
{
    /**
     * The assets root holding the format directories — `css/shapes`, `partials`,
     * `components`, `vue`. Usually the same directory the bundle hands the
     * loader, which is why this returns one path and not a list: an element of a
     * bundle is found under one root or it is found nowhere.
     */
    public static function getDesignSystemElementsPath(): string;
}
