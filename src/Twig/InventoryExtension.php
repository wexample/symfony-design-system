<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use JsonException;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Class\ElementCompilation;
use Wexample\SymfonyDesignSystem\Class\ElementInventory;
use Wexample\SymfonyDesignSystem\Service\ElementRegistryService;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

/**
 * Hands a template the registries of the bundles holding elements, merged.
 *
 * Two ways of answering the same question, and which one is used is decided by
 * the kernel rather than by the caller.
 *
 * In production it reads the written files and walks nothing: the page that
 * shows the registry is a consumer of it like any other, so a registry nobody
 * regenerated is a page that says so rather than a page that quietly disagrees
 * with the file everything else reads.
 *
 * In debug it compiles from the assets instead. A component written a minute
 * ago is on the page at the next reload, with no command to remember and no
 * build to wait for — and the walk being the slower answer costs nothing where
 * nobody is being served.
 */
class InventoryExtension extends AbstractExtension
{
    /**
     * The walk is worth doing once per request, not once per function the page
     * calls: the registry and its problems come out of the same compilation.
     */
    private ?ElementCompilation $compiled = null;

    public function __construct(
        private readonly ElementRegistryService $registryService,
        private readonly bool $debug,
    ) {
    }

    private function compile(Environment $twig): ElementCompilation
    {
        return $this->compiled ??= $this->registryService->compileAll($twig);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ds_element_registry',
                /**
                 * @throws JsonException
                 */
                function (Environment $twig): ElementInventory {
                    return $this->debug
                        ? $this->compile($twig)->inventory
                        : $this->registryService->loadAll();
                },
                [self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true]
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
                // written: what the page is missing, by name. Nothing is
                // missing in debug, where nothing is read.
                'ds_element_registry_missing',
                function (): array {
                    if ($this->debug) {
                        return [];
                    }

                    return array_map(
                        static fn ($source): string => $source->alias,
                        $this->registryService->findMissing()
                    );
                }
            ),
            new TwigFunction(
                /**
                 * Where a declaration and the assets contradict each other, one
                 * sentence per point. Only in debug: in production the file on
                 * disk was written by a compilation that had none, so there is
                 * nothing left to report.
                 */
                'ds_element_registry_problems',
                function (Environment $twig): array {
                    return $this->debug
                        ? $this->compile($twig)->problems
                        : [];
                },
                [self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true]
            ),
        ];
    }
}
