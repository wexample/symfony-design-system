<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Wexample\SymfonyDesignSystem\Service\PageService;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

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
        ];
    }

    public function pageInit(
        string $pageName
    ): void {
        $this->pageService->pageInit(
            $this->adaptiveResponseService->renderPass->layoutRenderNode->page,
            $pageName,
        );
    }
}
