<?php

namespace Wexample\SymfonyDesignSystem\Controller\Config\DesignSystem;

use Wexample\SymfonyDesignSystem\Controller\AbstractPagesController;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

abstract class AbstractAppController extends AbstractPagesController
{
    final public const ROUTE_INDEX = VariableHelper::INDEX;
    final public const ROUTE_LOGO = 'logo';
    final public const ROUTE_STRIPE = 'stripe';
    final public const ROUTE_CARD = 'card';

    public static function getSimpleRoutes(): array
    {
        return [
            self::ROUTE_INDEX,
            self::ROUTE_LOGO,
            self::ROUTE_STRIPE,
            self::ROUTE_CARD,
        ];
    }
}
