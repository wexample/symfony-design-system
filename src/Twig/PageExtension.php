<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Wexample\SymfonyDesignSystem\Service\PageService;

class PageExtension extends AbstractExtension
{
    public function __construct(
        private readonly AdaptiveResponseService $adaptiveResponseService,
        private readonly PageService $pageService,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'page_init',
                [
                    $this,
                    'pageInit',
                ]
            ),
            new TwigFunction(
                'page_name_from_route',
                [
                    $this,
                    'pageNameFromRoute',
                ]
            ),
        ];
    }

    public function pageInit(
        string $pageName
    ) {
        $this->pageService->pageInit(
            $this->adaptiveResponseService->renderPass->layoutRenderNode->page,
            $pageName,
            $this->adaptiveResponseService->renderPass->layoutRenderNode->useJs
        );
    }

    public function pageNameFromRoute(string $route): string
    {
        return $this->pageService->buildPageNameFromClassPath(
            $this->pageService->getControllerClassPathFromRouteName(
                $route
            )
        );
    }
}
