<?php

namespace Wexample\SymfonyDesignSystem\Controller\Config;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

#[Route(path: DemoController::CONTROLLER_BASE_ROUTE . '/demo/', name: DemoController::CONTROLLER_BASE_ROUTE . '_demo_')]
final class DemoController extends AbstractDesignSystemShowcaseController
{
    final public const ROUTE_INDEX = VariableHelper::INDEX;

    #[Route(path: '', name: self::ROUTE_INDEX)]
    final public function index(): Response
    {
        return $this->renderPage(
            self::ROUTE_INDEX
        );
    }
}
