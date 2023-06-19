<?php

namespace Wexample\SymfonyDesignSystem\Service;

class DesignSystemConfigurationService
{
    private array $frontPaths = [];

    public function addFrontPath(string $path): void
    {
        $this->frontPaths[] = realpath($path);
    }

    /**
     * @return array
     */
    public function getFrontPaths(): array
    {
        return $this->frontPaths;
    }
}
