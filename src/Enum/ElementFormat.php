<?php

namespace Wexample\SymfonyDesignSystem\Enum;

/**
 * How an element of the design system is handed over to whoever draws it.
 *
 * A format is not what an element *is* — that is its nature, which nothing here
 * declares yet — but the shape it is delivered in. The five below are what the
 * bundle ships today; a json schema, an agent prompt and foreign frameworks are
 * meant to join them, which is why the list is named in one place instead of
 * being read off a directory listing each time it is needed.
 */
enum ElementFormat: string
{
    /**
     * A css class expecting markup it does not itself produce.
     */
    case SHAPE = 'shape';

    /**
     * A twig include drawing that markup, taking its variables from the caller.
     */
    case PARTIAL = 'partial';

    /**
     * A php function drawing it, with named options and defaults.
     */
    case TWIG_FUNCTION = 'twig_function';

    /**
     * The loader's triad — template, script, stylesheet — bound at runtime, and
     * the only format that can carry behaviour.
     */
    case COMPONENT = 'component';

    /**
     * The client-side twin, drawn in the browser from the same options.
     */
    case VUE = 'vue';

    /**
     * Where under `assets/` the format's primary files are looked for.
     */
    public function getDirectory(): ?string
    {
        return match ($this) {
            self::SHAPE => 'css/shapes',
            self::PARTIAL => 'partials',
            self::COMPONENT => 'components',
            self::VUE => 'vue',
            // Declared in php, so there is no directory to walk.
            self::TWIG_FUNCTION => null,
        };
    }

    /**
     * @return self[]
     */
    public static function fileBased(): array
    {
        return array_filter(
            self::cases(),
            static fn (self $format): bool => $format->getDirectory() !== null
        );
    }
}
