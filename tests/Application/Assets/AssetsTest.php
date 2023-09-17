<?php

namespace Wexample\SymfonyDesignSystem\Tests\Application\Assets;

use Wexample\SymfonyApi\Api\Controller\Test\ResponseController;
use Wexample\SymfonyDesignSystem\Controller\Pages\DemoController;
use Wexample\SymfonyDesignSystem\Tests\AbstractDesignSystemTestCase;
use Wexample\SymfonyHelpers\Helper\VariableHelper;
use function count;

class AssetsTest extends AbstractDesignSystemTestCase
{
    public function testAssetsLoading()
    {
        $this->createGlobalClient();

        $this->goToRoute(VariableHelper::DEMO.'_'.VariableHelper::ASSETS);

        ResponseController::buildRouteName(DemoController::ROUTE_ASSETS);

        $layoutRenderData = $this->getPageLayoutData();

        $this->assertNotEmpty(
            $layoutRenderData,
            'Html contains layout data.'
        );

        $this->assertRenderData($layoutRenderData);

        $this->assertTrue(
            isset($layoutRenderData['page']),
            'Layout data contains page data'
        );

        $this->assertRenderData($layoutRenderData['page']);

        $pageRenderData = $layoutRenderData['page'];

        $this->assertTrue(
            isset($pageRenderData['assets']['css']),
            'Demo page contains a default css page.'
        );

        $this->assertNotEmpty(
            count($pageRenderData['assets']['css']),
            'Demo page contains some css page.'
        );
    }

    protected function assertRenderData(array $renderData)
    {
        $this->assertTrue(
            isset($renderData['assets']),
            'Render data contains assets entry'
        );
    }
}
