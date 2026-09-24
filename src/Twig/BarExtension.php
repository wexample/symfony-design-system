<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

class BarExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'bar',
                function (
                    Environment $twig,
                    $context,
                    string $title,
                    array $options = []
                ): string {
                    $context = is_array($context) ? $context : [];

                    return $this->componentsExtension->component(
                        $twig,
                        $context['render_pass'] ?? null,
                        '@WexampleSymfonyDesignSystemBundle/components/bar',
                        [
                            'title' => $title,
                            'subtitle' => $options['subtitle'] ?? null,
                            'icon' => $options['icon'] ?? null,
                            // Markup slots, rendered as given: what stands before
                            // the title when an icon is not enough, and what
                            // stands at the far end.
                            'avatar' => $options['avatar'] ?? null,
                            'trailing' => $options['trailing'] ?? null,
                            // What stands in the middle when a title is not
                            // what the line says: a figure, a bar of parts.
                            'body' => $options['body'] ?? null,
                            'href' => $options['href'] ?? null,
                            'attr' => $options['attr'] ?? [],
                            'class' => $this->buildClass($options),
                        ]
                    );
                },
                [
                    self::FUNCTION_OPTION_IS_SAFE => self::FUNCTION_OPTION_IS_SAFE_VALUE_HTML,
                    self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true,
                    self::FUNCTION_OPTION_NEEDS_CONTEXT => true,
                ]
            ),
        ];
    }

    private function buildClass(array $options): string
    {
        $classes = ['bar'];

        if (! empty($options['class'])) {
            $classes[] = $options['class'];
        }

        return implode(' ', $classes);
    }
}
