<?php

namespace Wexample\SymfonyDesignSystem\Tests\Application\Role\Anonymous\Controller\Pages;

use Wexample\SymfonyDesignSystem\Controller\Pages\DemoController;
use Wexample\SymfonyDesignSystem\Tests\Traits\DesignSystemTestCaseTrait;
use Wexample\SymfonyDesignSystem\Traits\SymfonyDesignSystemBundleClassTrait;
use Wexample\SymfonyTesting\Tests\AbstractRoleControllerTestCase;
use Wexample\SymfonyTesting\Tests\Traits\RoleAnonymousTestCaseTrait;
use Wexample\SymfonyTesting\Traits\ControllerTestCaseTrait;

class TestControllerTest extends AbstractRoleControllerTestCase
{
    use RoleAnonymousTestCaseTrait;
    use ControllerTestCaseTrait;
    use SymfonyDesignSystemBundleClassTrait;
    use DesignSystemTestCaseTrait;

    public function testIndex()
    {
        $this->goToControllerRouteAndCheckHtml(
            DemoController::ROUTE_INDEX
        );
    }
}