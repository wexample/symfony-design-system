<?php

namespace Wexample\SymfonyDesignSystem\Enum;

/**
 * A way of drawing a component, among the several it keeps side by side.
 *
 * There is one kind of thing in the design system — a component — and a folder
 * per component holding every renderer it has: the stylesheet, the server
 * template, the client twin, the behaviour. A format is one of those files, not
 * a rival way of shipping the element, which is what the old split between
 * shapes, partials, components and functions had turned into.
 *
 * Adding a format is adding a case here and a file beside the others:
 * `button.tsx` for react, `button.prompt.md` for an agent. Nothing else in the
 * registry has to learn about it.
 */
enum ElementFormat: string
{
    /**
     * The stylesheet. Loaded by the loader when the component is on the page,
     * so nothing has to import it by hand.
     */
    case STYLE = 'style';

    /**
     * The server template, rendered by twig. `.front.html.twig` beside it is
     * the same renderer for a component the browser clones.
     */
    case TEMPLATE = 'template';

    /**
     * The client twin, drawn by vue from the same options.
     */
    case VUE = 'vue';

    /**
     * The behaviour, bound to the markup once it is in the document. Only for a
     * component that does something.
     */
    case SCRIPT = 'script';

    /**
     * What a file has to end with to be that renderer of its component.
     *
     * The order matters: `.vue.twig` is a vue wrapper and not a template, so it
     * has to be tried before `.html.twig` would claim it.
     */
    public function getSuffixes(): array
    {
        return match ($this) {
            self::STYLE => ['.scss'],
            self::VUE => ['.vue', '.vue.twig'],
            self::TEMPLATE => ['.html.twig', '.front.html.twig'],
            self::SCRIPT => ['.ts'],
        };
    }

    /**
     * The one file whose presence means the component has that renderer at all.
     * A `.vue.twig` without its `.vue` is a wrapper around nothing.
     */
    public function getPrimarySuffix(): string
    {
        return match ($this) {
            self::STYLE => '.scss',
            self::VUE => '.vue',
            self::TEMPLATE => '.html.twig',
            self::SCRIPT => '.ts',
        };
    }
}
