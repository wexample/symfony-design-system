<?php

namespace Wexample\SymfonyDesignSystem\Controller\Config\DesignSystem;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wexample\SymfonyDesignSystem\Controller\AbstractPagesController;
use Wexample\SymfonyDesignSystem\Traits\SymfonyDesignSystemBundleClassTrait;

#[Route(path: '_design_system/project/', name: '_design_system_project_')]
final class ProjectController extends AbstractPagesController
{
    use SymfonyDesignSystemBundleClassTrait;

    final public const ROUTE_LOGO = 'logo';

    #[Route(name: self::ROUTE_LOGO)]
    public function logo(): Response
    {
        return $this->renderPage(
            self::ROUTE_LOGO,
        );
    }
}
