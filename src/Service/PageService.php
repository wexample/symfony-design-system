<?php

namespace Wexample\SymfonyDesignSystem\Service;

use JetBrains\PhpStorm\Pure;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\PageRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyTranslations\Translation\Translator;

class PageService extends RenderNodeService
{
    #[Pure]
    public function __construct(
        AssetsService $assetsService,
        AdaptiveResponseService $adaptiveResponseService,
        protected Translator $translator,
    ) {
        parent::__construct(
            $assetsService,
            $adaptiveResponseService
        );
    }

    public function pageInit(
        RenderPass $renderPass,
        PageRenderNode $page,
        string $pageName,
    ): void {
        $this->initRenderNode(
            $renderPass,
            $page,
            $pageName,
        );

        $this->translator->setDomainFromPath(
            $page->getContextType(),
            $pageName
        );
    }
}
