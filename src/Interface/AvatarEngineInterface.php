<?php

namespace Wexample\SymfonyDesignSystem\Interface;

/**
 * A way of drawing a face for whoever has no picture: initials on a colour,
 * the wexies of symfony-design-system-effects, the next one. Given the same
 * seed — an identifier that does not change, never a name, which does — an
 * engine draws the same face every time.
 */
interface AvatarEngineInterface
{
    public const string TAG = 'wexample_symfony_design_system.avatar_engine';

    /**
     * What the engine is asked by: `initials`, `wexies-2020`.
     */
    public static function getName(): string;

    /**
     * The face as a standalone SVG document, square, with a viewBox and no
     * size: what holds it says how large. `$label` is the name the face
     * stands for, for an engine that writes something of it.
     */
    public function render(
        string $seed,
        ?string $label = null
    ): string;
}
