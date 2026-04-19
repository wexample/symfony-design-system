<?php

namespace Wexample\SymfonyDesignSystem\Controller\Pages\DesignSystem;

use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyLoader\Controller\Pages\AbstractDesignSystemController;
use Wexample\SymfonyRouting\Attribute\TemplateBasedRoutes;

#[Route(
    name: 'wexample_design_system_generic_',
    path: AbstractDesignSystemController::CONTROLLER_BASE_ROUTE . '/generic/',
)]
#[TemplateBasedRoutes]
final class GenericController extends AbstractDesignSystemGenericController
{
}
