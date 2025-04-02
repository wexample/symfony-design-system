<?php

namespace Wexample\SymfonyDesignSystem\Controller\Config\DesignSystem;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wexample\SymfonyDesignSystem\Controller\AbstractPagesController;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

abstract class AbstractAppController extends AbstractPagesController
{
    final public const ROUTE_INDEX = VariableHelper::INDEX;
    final public const ROUTE_LOGO = 'logo';
    final public const ROUTE_STRIPE = 'stripe';

    #[Route(name: self::ROUTE_INDEX)]
    public function index(): Response
    {
        return $this->renderPage(
            self::ROUTE_INDEX,
        );
    }

    #[Route(path: self::ROUTE_LOGO, name: self::ROUTE_LOGO)]
    public function logo(): Response
    {
        return $this->renderPage(
            self::ROUTE_LOGO,
        );
    }

    #[Route(path: self::ROUTE_STRIPE, name: self::ROUTE_STRIPE)]
    public function stripe(): Response
    {
        return $this->renderPage(
            self::ROUTE_STRIPE,
        );
    }
}
