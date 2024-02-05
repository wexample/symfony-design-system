<?php

namespace Wexample\SymfonyDesignSystem\Service;

use JetBrains\PhpStorm\Pure;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\PageRenderNode;
use Wexample\SymfonyTranslations\Translation\Translator;

class PageService extends RenderNodeService
{
    #[Pure]
    public function __construct(
        protected Translator $translator,
    ) {

    }

    public function pageInit(
        PageRenderNode $page,
        string $pageName,
    ): void {
        $this->initRenderNode(
            $page,
            $pageName,
        );

        $this->translator->setDomainFromPath(
            $page->getContextType(),
            $pageName
        );
    }
}
