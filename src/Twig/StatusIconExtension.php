<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

class StatusIconExtension extends AbstractTemplateExtension
{
    public function __construct(
        private readonly ComponentsExtension $componentsExtension,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'status_icon',
                function (
                    Environment $twig,
                    $context,
                    string $type,
                    array $options = []
                ): string {
                    $context = is_array($context) ? $context : [];

                    return $this->componentsExtension->component(
                        $twig,
                        $context['render_pass'] ?? null,
                        '@WexampleSymfonyDesignSystemBundle/components/status-icon',
                        [
                            'type' => $type,
                            // How much of the ring is drawn, 0 to 1. Null leaves
                            // it whole, which is what every state but a running
                            // one means.
                            'progress' => $options['progress'] ?? null,
                            // Overrides what the type would put inside the ring.
                            'glyph_name' => $options['glyph'] ?? null,
                            'size' => $options['size'] ?? null,
                            // Given a label the circle is read out, without one it
                            // is decoration beside something that already says it.
                            'label' => $options['label'] ?? null,
                            'class' => $options['class'] ?? null,
                            'attr' => $options['attr'] ?? [],
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
}
