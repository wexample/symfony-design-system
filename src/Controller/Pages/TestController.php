<?php

namespace Wexample\SymfonyDesignSystem\Controller\Pages;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wexample\SymfonyDesignSystem\Controller\AbstractPagesController;
use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;

abstract class TestController extends AbstractPagesController
{
    #[Route(path: '_core/test/view', name: '_core_test_view', options: self::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function view(): Response
    {
        return $this->render(
            WexampleSymfonyDesignSystemBundle::getAlias() . '/Resources/templates/pages/_core/test/view.html.twig'
        );
    }
}
