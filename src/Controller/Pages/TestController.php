<?php

namespace Wexample\SymfonyDesignSystem\Controller\Pages;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wexample\SymfonyDesignSystem\Controller\AbstractPagesController;
use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;
use Wexample\SymfonyHelpers\Helper\RequestHelper;

abstract class TestController extends AbstractPagesController
{
    #[Route(path: '_core/test', name: '_core_test_index')]
    public function index(RequestStack $requestStack): Response
    {
        // Allow parameter to disable aggregation.
        $this->enableAggregation = RequestHelper::getQueryBoolean(
            $requestStack->getMainRequest(),
            'no-aggregation'
        ) ? false : $this->enableAggregation;

        return $this->renderPage(
            '_core/test/index'
        );
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '_core/test/adaptive', name: '_core_test_adaptive', options: self::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function adaptive(): Response
    {
        return $this
            ->adaptiveResponseService
            ->createResponse($this)
            ->setView(
                $this->buildTemplatePath('_core/test/adaptive')
            )
            ->render();
    }

    #[Route(path: '_core/test/view', name: '_core_test_view', options: self::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function view(): Response
    {
        return $this->render(
            '@'.WexampleSymfonyDesignSystemBundle::getAlias().'/pages/_core/test/view.html.twig'
        );
    }

    /**
     * @throws \Exception
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
