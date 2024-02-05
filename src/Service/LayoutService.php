<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Exception;
use JetBrains\PhpStorm\Pure;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractLayoutRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyTranslations\Translation\Translator;

class LayoutService extends RenderNodeService
{
    #[Pure]
    public function __construct(
        protected AdaptiveResponseService $adaptiveResponseService,
        protected Translator $translator
    ) {
        parent::__construct(
            $adaptiveResponseService
        );
    }

    /**
     * @throws Exception
     */
    public function layoutInitInitial(
        RenderPass $renderPass,
        Environment $twig,
        string $layoutName,
    ): void {
        $this->layoutInit(
            $twig,
            $renderPass->layoutRenderNode,
            $layoutName,
        );
    }

    /**
     * @throws Exception
     */
    public function layoutInit(
        Environment $twig,
        AbstractLayoutRenderNode $layoutRenderNode,
        string $layoutName,
    ) {
        $this->initRenderNode(
            $layoutRenderNode,
            $layoutName,
        );
    }
}
