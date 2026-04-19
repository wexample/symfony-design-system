<?php

namespace Wexample\SymfonyDesignSystem\Controller\Pages\DesignSystem\Generic;

use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyDesignSystem\Controller\Pages\DesignSystem\AbstractDesignSystemGenericController;
use Wexample\SymfonyLoader\Controller\Pages\AbstractDesignSystemController;
use Wexample\SymfonyRouting\Attribute\TemplateBasedRoutes;

#[Route(
    name: 'wexample_design_system_generic_form_',
    path: AbstractDesignSystemController::CONTROLLER_BASE_ROUTE . '/generic/form/',
)]
#[TemplateBasedRoutes]
final class FormController extends AbstractDesignSystemGenericController
{
    // Note: 'rendered' and 'ajax' demo pages require form processors.
    // These are provided by the consuming app by overriding this controller,
    // or by installing a dedicated demo form package.
    // Template-based routes for index and vue are auto-generated.
}
