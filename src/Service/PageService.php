<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Wexample\SymfonyTranslations\Translation\Translator;
use Wexample\WebRenderNode\Rendering\RenderNode\PageRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;

class PageService extends AbstractRenderNodeService
{
    public function __construct(
        AssetsService $assetsService,
        protected Translator $translator,
    )
    {
        parent::__construct(
            $assetsService,
        );
    }

    public function pageInit(
        RenderPass $renderPass,
        PageRenderNode $page,
        string $view
    ): void
    {
        $this->initRenderNode(
            $page,
            $renderPass,
            $view
        );
	
        $this->translator->setDomainFromTemplatePath(
            $page->getContextType(),
            $view
        );
    }
}
