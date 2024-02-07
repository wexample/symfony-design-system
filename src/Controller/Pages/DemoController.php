<?php

namespace Wexample\SymfonyDesignSystem\Controller\Pages;

use Exception;
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

    protected string $viewPathPrefix = VariableHelper::DEMO.'/';

    #[Route(path: '', name: self::ROUTE_INDEX)]
    public function index(): Response
    {
        return $this->renderPage(
            self::ROUTE_INDEX,
            bundle: WexampleSymfonyDesignSystemBundle::class
        );
    }

    #[Route(
        path: VariableHelper::ASSETS,
        name: self::ROUTE_ASSETS
    )]
    public function assets(): Response
    {
        return $this->renderPage(
            VariableHelper::ASSETS,
            [
                'displayBreakpoints' => AssetsService::DISPLAY_BREAKPOINTS,
            ]
        );
    }

    #[Route(
        path: VariableHelper::PLURAL_COMPONENT,
        name: VariableHelper::PLURAL_COMPONENT
    )]
    public function components(): Response
    {
        return $this->renderPage(
            VariableHelper::PLURAL_COMPONENT
        );
    }

    #[Route(
        path: VariableHelper::LOADING,
        name: VariableHelper::LOADING
    )]
    public function loading(): Response
    {
        return $this->renderPage(
            VariableHelper::LOADING
        );
    }

    /**
     * @throws Exception
     */
    #[Route(
        path: VariableHelper::LOADING.'/fetch/simple',
        name: VariableHelper::LOADING.'_fetch_simple'
    )]
    public function loadingFetchSimple(): Response
    {
        return $this
            ->adaptiveResponseService
            ->createResponse($this)
            ->setView(
                $this->buildTemplatePath('loading-fetch-simple')
            )
            ->render();
    }

    #[Route(
        path: VariableHelper::TRANSLATIONS,
        name: VariableHelper::TRANSLATIONS
    )]
    public function translations(): Response
    {
        return $this->renderPage(
            VariableHelper::TRANSLATIONS
        );
    }
}
