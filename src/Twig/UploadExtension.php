<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Class\UploadRules;
use Wexample\SymfonyDesignSystem\Service\ChunkedUploadReceiver;
use Wexample\SymfonyDesignSystem\Service\UploadTokenService;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

/**
 * Offering an upload into one directory: the address files are sent to,
 * signed for that directory (`upload_url()`), and the place they are dropped
 * on or picked from (`upload_dropzone()`). The directory is the page's to
 * choose and the browser's to use, never to change.
 */
class UploadExtension extends AbstractTemplateExtension
{
    public function __construct(
        ComponentsExtension $componentsExtension,
        private readonly UploadTokenService $tokens,
        private readonly ChunkedUploadReceiver $receiver,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct($componentsExtension);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('upload_url', $this->uploadUrl(...)),
            // Options: `name`, said again by the events of what it sends;
            // `label`, the button's words; `hint`, the words beside it;
            // `compact`, a line rather than a block; `button`, the button alone;
            // `class`. `accept` — `['.xlsx']`, `'image/*'`, as the HTML
            // attribute reads — and `max_size`, in bytes, are signed into the
            // address and held by the server; the picker shows only the kinds
            // accepted.
            new TwigFunction(
                'upload_dropzone',
                function (Environment $twig, $context, string $directory, array $options = []): string {
                    $rules = new UploadRules(
                        array_values((array) ($options['accept'] ?? [])),
                        $options['max_size'] ?? null,
                    );

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/dropzone',
                        [
                            'label' => $options['label'] ?? null,
                            'hint' => $options['hint'] ?? null,
                            'compact' => $options['compact'] ?? false,
                            'button' => $options['button'] ?? false,
                            'class' => $options['class'] ?? null,
                            'accept' => implode(',', $rules->accept),
                        ],
                        [
                            'url' => $this->uploadUrl($directory, $rules),
                            'chunkSize' => $this->receiver->chunkSize(),
                            'name' => $options['name'] ?? null,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }

    public function uploadUrl(string $directory, ?UploadRules $rules = null): string
    {
        return $this->urlGenerator->generate('wexample_design_system_upload_chunk', [
            'token' => $this->tokens->issue($directory, rules: $rules),
        ]);
    }
}
