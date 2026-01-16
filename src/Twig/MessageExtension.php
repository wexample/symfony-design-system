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
                function (Environment $twig, string $title, ?string $body = null, array $options = []) {
                    $options['icon'] = $options['icon'] ?? 'ph:bold/info';
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/components/message.html.twig',
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
                function (Environment $twig, string $title, ?string $body = null, array $options = []) {
                    $options['icon'] = $options['icon'] ?? 'ph:bold/check-circle';
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/components/message.html.twig',
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
                function (Environment $twig, string $title, ?string $body = null, array $options = []) {
                    $options['icon'] = $options['icon'] ?? 'ph:bold/warning';
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/components/message.html.twig',
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
                function (Environment $twig, string $title, ?string $body = null, array $options = []) {
                    $options['icon'] = $options['icon'] ?? 'ph:bold/x-circle';
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/components/message.html.twig',
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
