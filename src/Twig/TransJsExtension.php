<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;
use Wexample\SymfonyTranslations\Translation\Translator;

class TransJsExtension extends AbstractExtension
{
    public function __construct(
        protected AdaptiveResponseService $adaptiveResponseService,
        protected Translator $translator
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'trans_js',
                [
                    $this,
                    'transJs',
                ]
            ),
        ];
    }

    /**
     * Make translation available for javascript.
     */
    public function transJs(
        string|array $keys
    ): void {
        $keys = is_string($keys) ? [$keys] : $keys;

        $currentRenderNode = $this
            ->adaptiveResponseService
            ->renderPass
            ->getCurrentContextRenderNode();

        foreach ($keys as $key) {
            $currentRenderNode->translations += $this->translator->transFilter($key);
        }
    }
}
