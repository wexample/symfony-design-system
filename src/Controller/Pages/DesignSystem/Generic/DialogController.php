<?php

namespace Wexample\SymfonyDesignSystem\Controller\Pages\DesignSystem\Generic;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyDesignSystem\Controller\Pages\DesignSystem\AbstractDesignSystemGenericController;
use Wexample\SymfonyHelpers\Controller\AbstractController;
use Wexample\SymfonyLoader\Controller\Pages\AbstractDesignSystemController;
use Wexample\SymfonyRouting\Attribute\TemplateBasedRoutes;

#[Route(
    name: 'wexample_design_system_generic_dialog_',
    path: AbstractDesignSystemController::CONTROLLER_BASE_ROUTE . '/generic/dialog/',
)]
#[TemplateBasedRoutes]
final class DialogController extends AbstractDesignSystemGenericController
{
    final public const ROUTE_MODAL_TEST_SIMPLE = 'modal_test_simple';
    final public const ROUTE_MODAL_TEST_COMPONENTS = 'modal_test_components';
    final public const ROUTE_MODAL_TEST_MEDIUM = 'modal_test_medium';
    final public const ROUTE_MODAL_TEST_LONG = 'modal_test_long';

    #[Route(path: 'modal-test-simple', name: self::ROUTE_MODAL_TEST_SIMPLE, options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function modalTestSimple(): Response
    {
        return $this->renderPage(self::ROUTE_MODAL_TEST_SIMPLE);
    }

    #[Route(path: 'modal-test-components', name: self::ROUTE_MODAL_TEST_COMPONENTS, options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function modalTestComponents(): Response
    {
        return $this->renderPage(self::ROUTE_MODAL_TEST_COMPONENTS);
    }

    #[Route(path: 'modal-test-medium', name: self::ROUTE_MODAL_TEST_MEDIUM, options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function modalTestMedium(): Response
    {
        return $this->renderPage(self::ROUTE_MODAL_TEST_MEDIUM);
    }

    #[Route(path: 'modal-test-long', name: self::ROUTE_MODAL_TEST_LONG, options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function modalTestLong(): Response
    {
        return $this->renderPage(self::ROUTE_MODAL_TEST_LONG);
    }
}
