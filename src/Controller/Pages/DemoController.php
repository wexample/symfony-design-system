<?php

namespace Wexample\SymfonyDesignSystem\Controller\Pages;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wexample\SymfonyDesignSystem\Controller\AbstractPagesController;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyDesignSystem\Traits\SymfonyDesignSystemBundleClassTrait;
use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

#[Route(path: '_design_system/demo/', name: '_design_system_demo_')]
final class DemoController extends AbstractPagesController
{
    use SymfonyDesignSystemBundleClassTrait;

    final public const ROUTE_INDEX = VariableHelper::INDEX;
    final public const ROUTE_ASSETS = VariableHelper::ASSETS;

    protected string $viewPathPrefix = VariableHelper::DEMO.'/';

    #[Route(path: '', name: self::ROUTE_INDEX)]
    public function index(): Response
    {
        return $this->renderPage(
            self::ROUTE_INDEX
        );
    }

    #[Route(
        path: VariableHelper::ASSETS,
        name: self::ROUTE_ASSETS
    )]
    public function assets(): Response
    {
        return $this->renderPage(
            self::ROUTE_ASSETS,
            [
                'displayBreakpoints' => AssetsService::DISPLAY_BREAKPOINTS,
            ]
        );
    }
}
