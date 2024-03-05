<?php

namespace Wexample\SymfonyDesignSystem\Controller\Pages;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wexample\SymfonyDesignSystem\Controller\AbstractPagesController;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\Usage\FontsAssetUsageService;
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

    public static function getSimpleRoutes(): array
    {
        return [
            self::ROUTE_AGGREGATION,
            self::ROUTE_COLOR_SCHEMES,
            self::ROUTE_COMPONENTS,
            self::ROUTE_LOADING,
            self::ROUTE_TRANSLATIONS,
        ];
    }

    #[Route(path: '', name: self::ROUTE_INDEX)]
    public function index(): Response
    {
        return $this->renderPage(
            self::ROUTE_INDEX
        );
    }

    protected function configureRenderPass(
        RenderPass $renderPass
    ): RenderPass {
        $renderPass->setUseJs($this->useJs);

        $renderPass->setUsage(
            FontsAssetUsageService::getName(),
            'demo'
        );

        return $renderPass;
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
        );
    }
}
