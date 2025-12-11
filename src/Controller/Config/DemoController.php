<?php

namespace Wexample\SymfonyDesignSystem\Controller\Config;

use Symfony\Component\Routing\Annotation\Route;
use Wexample\SymfonyDesignSystem\Routing\Attribute\TemplateBasedRoutes;
use Wexample\SymfonyDesignSystem\Traits\SymfonyDesignSystemBundleClassTrait;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

#[Route(path: DemoController::CONTROLLER_BASE_ROUTE . '/demo/', name: DemoController::CONTROLLER_BASE_ROUTE . '_demo_')]
#[TemplateBasedRoutes]
final class DemoController extends AbstractDesignSystemShowcaseController
{
    // Trait exposes the bundle class so the route loader knows where to fetch the templates.
    use SymfonyDesignSystemBundleClassTrait;

    final public const ROUTE_INDEX = VariableHelper::INDEX;
}
