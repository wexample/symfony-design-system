<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Wexample\SymfonyDesignSystem\Helper\DesignSystemHelper;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyHelpers\Helper\JsonHelper;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

class DesignSystemConfigurationService
{
    private array $frontPaths = [];

    public function __construct(private readonly KernelInterface $kernel)
    {

    }

    public function addFrontPath(string $path): void
    {
        $this->frontPaths[] = realpath($path);
    }

    public function buildFrontsPathsList(): bool
    {
        return JsonHelper::write(
            $this->getFrontsListPath(),
            $this->getFrontPaths()
        );
    }

    public function getFrontsListPath(): string
    {
        return FileHelper::joinPathParts([
            $this->kernel->getProjectDir(),
            VariableHelper::ASSETS,
            DesignSystemHelper::TWIG_NAMESPACE_FRONT
            .FileHelper::EXTENSION_SEPARATOR
            .VariableHelper::JSON,
        ]);
    }

    /**
     * @return array
     */
    public function getFrontPaths(): array
    {
        return $this->frontPaths;
    }
}
