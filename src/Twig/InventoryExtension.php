<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use JsonException;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Class\ElementInventory;
use Wexample\SymfonyDesignSystem\Service\ElementRegistryService;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

/**
 * Hands a template the registries of the bundles holding elements, merged.
 *
 * It reads the written files and walks nothing: the page that shows the registry
 * is a consumer of it like any other, so a registry nobody regenerated is a page
 * that says so rather than a page that quietly disagrees with the file
 * everything else reads.
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
                function (): ElementInventory {
                    return $this->registryService->loadAll();
                }
            ),
            new TwigFunction(
                'ds_element_registry_sources',
                function (): array {
                    return array_map(
                        static fn ($source): string => $source->alias,
                        $this->registryService->getSources()
                    );
                }
            ),
            new TwigFunction(
                // The bundles that signed up but whose registry was never
                // written: what the page is missing, by name.
                'ds_element_registry_missing',
                function (): array {
                    return array_map(
                        static fn ($source): string => $source->alias,
                        $this->registryService->findMissing()
                    );
                }
            ),
        ];
    }
}
