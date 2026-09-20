<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

class MessageExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'message_info',
                function (Environment $twig, $context, string $title, ?string $body = null, array $options = []) {
                    $options['icon'] = $options['icon'] ?? 'ph:bold/info';

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/message',
                        [
                            'type' => 'info',
                            'title' => $title,
                            'body' => $body,
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
            new TwigFunction(
                'message_success',
                function (Environment $twig, $context, string $title, ?string $body = null, array $options = []) {
                    $options['icon'] = $options['icon'] ?? 'ph:bold/check-circle';

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/message',
                        [
                            'type' => 'success',
                            'title' => $title,
                            'body' => $body,
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
            new TwigFunction(
                'message_warning',
                function (Environment $twig, $context, string $title, ?string $body = null, array $options = []) {
                    $options['icon'] = $options['icon'] ?? 'ph:bold/warning';

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/message',
                        [
                            'type' => 'warning',
                            'title' => $title,
                            'body' => $body,
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
            new TwigFunction(
                'message_error',
                function (Environment $twig, $context, string $title, ?string $body = null, array $options = []) {
                    $options['icon'] = $options['icon'] ?? 'ph:bold/x-circle';

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/message',
                        [
                            'type' => 'error',
                            'title' => $title,
                            'body' => $body,
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
