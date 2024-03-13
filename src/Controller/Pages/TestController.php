<?php

namespace Wexample\SymfonyDesignSystem\Controller\Pages;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wexample\SymfonyDesignSystem\Controller\AbstractPagesController;
use Wexample\SymfonyDesignSystem\Service\Usage\FontsAssetUsageService;
use Wexample\SymfonyDesignSystem\Traits\SymfonyDesignSystemBundleClassTrait;
use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;

#[Route(path: '_design_system/test/', name: '_design_system_test_')]
final class TestController extends AbstractPagesController
{
    use SymfonyDesignSystemBundleClassTrait;

    final public const ROUTE_ADAPTIVE = 'adaptive';
    final public const ROUTE_INDEX = VariableHelper::INDEX;

    protected string $viewPathPrefix = VariableHelper::TEST.'/';

    #[Route(path: '', name: self::ROUTE_INDEX)]
    final public function index(Request $request): Response
    {
        $renderPass = $this->createPageRenderPass(self::ROUTE_INDEX);

        $renderPass->setUsage(
            FontsAssetUsageService::getName(),
            'demo'
        );

        $renderPass->enableAggregation = $request->get('test-aggregation', false);

        return $this->renderPage(
            self::ROUTE_INDEX,
            bundle: WexampleSymfonyDesignSystemBundle::class,
            renderPass: $renderPass
        );
    }

    /**
     * @throws Exception
     */
    #[Route(path: self::ROUTE_ADAPTIVE, name: self::ROUTE_ADAPTIVE, options: self::ROUTE_OPTIONS_ONLY_EXPOSE)]
    final public function adaptive(): Response
    {
        $renderPass = $this->createPageRenderPass(self::ROUTE_ADAPTIVE);

        return $this->renderPage(
            self::ROUTE_ADAPTIVE,
            renderPass:$renderPass
        );
    }

    #[Route(path: self::ROUTE_VIEW, name: self::ROUTE_VIEW, options: self::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function view(): Response
    {
        return $this->renderPage(self::ROUTE_VIEW);
    }

    /**
     * @throws Exception
     */
    #[Route(path: '_core/test/error-missing-view', name: '_core_test_error-missing-view', options: self::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function errorMissingVue(): Response
    {
        return $this
            ->adaptiveResponseService
            ->createResponse($this)
            ->setView(
                'MISSING_VIEW'
            )
            ->render();
    }
}
