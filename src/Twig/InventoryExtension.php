<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use JsonException;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Class\ElementInventory;
use Wexample\SymfonyDesignSystem\Service\ElementRegistryService;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

/**
 * Hands a template the registry of the bundle's elements.
 *
 * It reads the written file and does not walk the assets itself: the page that
 * shows the registry is a consumer of it like any other, so a registry nobody
 * regenerated is a page that says so rather than a page that quietly disagrees
 * with the file everything else reads.
 */
class InventoryExtension extends AbstractExtension
{
    public function __construct(
        private readonly ElementRegistryService $registryService,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ds_element_registry',
                /**
                 * @throws JsonException
                 */
                function (): ?ElementInventory {
                    return $this->registryService->load();
                }
            ),
            new TwigFunction(
                'ds_element_registry_path',
                function (): string {
                    return ElementRegistryService::RELATIVE_PATH;
                }
            ),
        ];
    }
}
