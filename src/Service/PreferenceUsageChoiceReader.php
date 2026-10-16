<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Wexample\SymfonyLoader\Interface\UsageChoiceReaderInterface;
use Wexample\SymfonyPreference\Service\PreferenceService;

/**
 * The usages a visitor chose, read where the interface keeps what it
 * remembers (symfony-preference, under `ui.`): what the colour scheme switch
 * writes as `ui.color_scheme` is what the next page is drawn with.
 */
class PreferenceUsageChoiceReader implements UsageChoiceReaderInterface
{
    private const string PREFIX = 'ui.';

    public function __construct(
        private readonly PreferenceService $preferences,
    ) {
    }

    public function chosenUsage(string $usageName): ?string
    {
        $chosen = $this->preferences->get(self::PREFIX.$usageName);

        return is_string($chosen) && '' !== $chosen ? $chosen : null;
    }
}
