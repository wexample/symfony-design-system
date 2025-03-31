<?php

namespace Wexample\SymfonyDesignSystem\Controller\Config;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wexample\SymfonyDesignSystem\Controller\AbstractPagesController;
use Wexample\SymfonyDesignSystem\Traits\SymfonyDesignSystemBundleClassTrait;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

#[Route(path: '_design_system/', name: '_design_system_')]
final class DesignSystemController extends AbstractPagesController
{
    use SymfonyDesignSystemBundleClassTrait;

    final public const ROUTE_INDEX = VariableHelper::INDEX;

    #[Route(name: self::ROUTE_INDEX)]
    public function index(): Response
    {
        return $this->renderPage(
            self::ROUTE_INDEX,
        );
    }
}
