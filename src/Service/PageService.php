<?php

namespace Wexample\SymfonyDesignSystem\Service;

use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpKernel\KernelInterface;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\PageRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyTranslations\Translation\Translator;

class PageService extends RenderNodeService
{
    #[Pure]
    public function __construct(
        AssetsService $assetsService,
        AdaptiveResponseService $adaptiveResponseService,
        KernelInterface $kernel,
        protected Translator $translator,
    ) {
        parent::__construct(
            $assetsService,
            $adaptiveResponseService,
            $kernel
        );
    }

    public function pageInit(
        RenderPass $renderPass,
        PageRenderNode $page,
        string $pagePath,
    ): void {
        $this->initRenderNode(
            $renderPass,
            $page,
            $pagePath,
        );

        $this->translator->setDomainFromPath(
            $page->getContextType(),
            $pagePath
        );
    }
}
