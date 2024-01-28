<?php

namespace Wexample\SymfonyDesignSystem\Controller\Pages;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wexample\SymfonyDesignSystem\Controller\AbstractPagesController;
use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

#[Route(path: '_design_system/demo/', name: '_design_system_demo_')]
class DemoController extends AbstractPagesController
{
    protected string $viewPathPrefix = VariableHelper::DEMO.'/';

    #[Route(path: '', name: VariableHelper::INDEX)]
    public function index(): Response
    {
        return $this->renderPage(
            VariableHelper::INDEX,
            bundle: WexampleSymfonyDesignSystemBundle::class
        );
    }
}
