<?php

namespace Wexample\SymfonyDesignSystem\Controller\Pages;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wexample\SymfonyDesignSystem\Controller\AbstractPagesController;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Traits\SymfonyDesignSystemBundleClassTrait;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

#[Route(path: '_design_system/demo/', name: '_design_system_demo_')]
final class DemoController extends AbstractPagesController
{
    use SymfonyDesignSystemBundleClassTrait;

    final public const ROUTE_INDEX = VariableHelper::INDEX;
    final public const ROUTE_ASSETS = VariableHelper::ASSETS;
    final public const ROUTE_AGGREGATION = 'aggregation';
    final public const ROUTE_COLOR_SCHEMES = 'color_schemes';
    final public const ROUTE_LOADING = VariableHelper::LOADING;
    final public const ROUTE_TRANSLATIONS = VariableHelper::TRANSLATIONS;
    final public const ROUTE_COMPONENTS = VariableHelper::PLURAL_COMPONENT;

    protected string $viewPathPrefix = VariableHelper::DEMO.'/';

    private bool $useJs = true;

    #[Route(path: '', name: self::ROUTE_INDEX)]
    public function index(): Response
    {
        return $this->renderPage(
            self::ROUTE_INDEX
        );
    }

    protected function createRenderPass(
        string $view
    ): RenderPass {
        $pass = parent::createRenderPass(
            $view
        );

        $pass->setUseJs($this->useJs);

        return $pass;
    }

    #[Route(
        path: VariableHelper::ASSETS,
        name: self::ROUTE_ASSETS
    )]
    public function assets(Request $request): Response
    {
        $this->useJs = !$request->get('no_js');

        return $this->renderPage(
            self::ROUTE_ASSETS,
            [
                'displayBreakpoints' => $this->getParameter('design_system.display_breakpoints'),
            ]
        );
    }

    public static function getSimpleRoutes(): array
    {
        return [
            'color_schemes',
        ];
    }

    #[Route(
        path: self::ROUTE_AGGREGATION,
        name: self::ROUTE_AGGREGATION
    )]
    public function aggregation(): Response
    {
        return $this->renderPage(
            self::ROUTE_AGGREGATION
        );
    }

    #[Route(
        path: VariableHelper::LOADING,
        name: self::ROUTE_LOADING
    )]
    public function loading(): Response
    {
        return $this->renderPage(
            self::ROUTE_LOADING
        );
    }

    #[Route(
        path: VariableHelper::TRANSLATIONS,
        name: self::ROUTE_TRANSLATIONS
    )]
    public function translations(): Response
    {
        return $this->renderPage(
            self::ROUTE_TRANSLATIONS
        );
    }

    #[Route(
        path: VariableHelper::PLURAL_COMPONENT,
        name: self::ROUTE_COMPONENTS
    )]
    public function components(): Response
    {
        return $this->renderPage(
            self::ROUTE_COMPONENTS
        );
    }
}
