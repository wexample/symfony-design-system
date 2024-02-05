<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Exception;
use JetBrains\PhpStorm\Pure;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractLayoutRenderNode;
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
        Environment $twig,
        string $layoutName,
    ) {
        $renderPass = $this->adaptiveResponseService->renderPass;

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
