<?php

namespace Wexample\SymfonyDesignSystem\Controller\Pages;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wexample\SymfonyDesignSystem\Controller\AbstractPagesController;
use Wexample\SymfonyDesignSystem\Service\Usage\FontsAssetUsageService;
use Wexample\SymfonyDesignSystem\Traits\SymfonyDesignSystemBundleClassTrait;
use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

#[Route(path: '_design_system/test/', name: '_design_system_test_')]
final class TestController extends AbstractPagesController
{
    use SymfonyDesignSystemBundleClassTrait;

    final public const ROUTE_INDEX = VariableHelper::INDEX;

    protected string $viewPathPrefix = VariableHelper::TEST.'/';

    #[Route(path: '', name: self::ROUTE_INDEX)]
    public function index(Request $request): Response
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
}
