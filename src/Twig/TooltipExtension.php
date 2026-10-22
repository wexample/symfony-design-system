<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * The attributes that give an element a tooltip: `<button {{ tooltip('Save') }}>`.
 * The page's single tooltip, placed by the layout, reads them; a vue binds the
 * same attributes.
 */
class TooltipExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('tooltip', $this->tooltip(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param array{title?: string, placement?: string, variant?: string} $options
     */
    public function tooltip(?string $text, array $options = []): string
    {
        if (null === $text || '' === $text) {
            return '';
        }

        $attributes = ['data-tooltip' => $text];

        foreach (['title', 'placement', 'variant'] as $option) {
            if (! empty($options[$option])) {
                $attributes['data-tooltip-'.$option] = $options[$option];
            }
        }

        return implode(' ', array_map(
            static fn (string $name, string $value): string => $name.'="'.htmlspecialchars($value, ENT_QUOTES).'"',
            array_keys($attributes),
            $attributes
        ));
    }
}
