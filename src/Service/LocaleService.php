<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Wexample\SymfonyDesignSystem\Translation\Translator;

class LocaleService
{
    public function __construct(
        protected AdaptiveResponseService $adaptiveResponseService,
        protected Translator $translator
    ) {
    }

    public function transJs(
        string|array $keys,
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
