<?php

namespace Wexample\SymfonyDesignSystem\Controller\Pages\DesignSystem\Generic;

use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyDesignSystem\Controller\Pages\DesignSystem\AbstractDesignSystemGenericController;
use Wexample\SymfonyLoader\Controller\Pages\AbstractDesignSystemController;
use Wexample\SymfonyRouting\Attribute\TemplateBasedRoutes;

#[Route(
    name: 'wexample_design_system_generic_layout_',
    path: AbstractDesignSystemController::CONTROLLER_BASE_ROUTE . '/generic/layout/',
)]
#[TemplateBasedRoutes]
final class LayoutController extends AbstractDesignSystemGenericController
{
}
