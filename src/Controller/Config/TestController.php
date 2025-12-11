<?php

namespace Wexample\SymfonyDesignSystem\Controller\Config;

use Symfony\Component\Routing\Annotation\Route;
use Wexample\SymfonyDesignSystem\Routing\Attribute\TemplateBasedRoutes;
use Wexample\SymfonyDesignSystem\Traits\SymfonyDesignSystemBundleClassTrait;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

#[Route(path: DemoController::CONTROLLER_BASE_ROUTE . '/test/', name: DemoController::CONTROLLER_BASE_ROUTE . '_test_')]
#[TemplateBasedRoutes]
final class TestController extends AbstractDesignSystemShowcaseController
{
    use SymfonyDesignSystemBundleClassTrait;

    final public const ROUTE_INDEX = VariableHelper::INDEX;
}
