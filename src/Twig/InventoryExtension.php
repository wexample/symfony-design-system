<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Class\ElementInventory;
use Wexample\SymfonyDesignSystem\Service\ElementScannerService;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

/**
 * Hands the demo the scan of the bundle's own elements.
 *
 * The page showing it holds no list: it draws whatever the scan found, so it
 * stops being true the moment the assets do, and not a release later.
 */
class InventoryExtension extends AbstractExtension
{
    public function __construct(
        private readonly ElementScannerService $scannerService,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ds_element_inventory',
                function (Environment $twig): ElementInventory {
                    return $this->scannerService->scan($twig);
                },
                [
                    self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true,
                ]
            ),
        ];
    }
}
