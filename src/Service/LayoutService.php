<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Exception;
use JetBrains\PhpStorm\Pure;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Rendering\AdaptiveResponse;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractLayoutRenderNode;
use Wexample\SymfonyTranslations\Translation\Translator;
use function array_merge;

class LayoutService extends RenderNodeService
{
    #[Pure]
    public function __construct(
        protected AdaptiveResponseService $adaptiveResponseService,
        protected AssetsService $assetsService,
        protected ComponentService $componentService,
        protected Translator $translator
    ) {
        parent::__construct(
            $assetsService,
            $adaptiveResponseService
        );
    }

    /**
     * @throws Exception
     */
    public function layoutInitInitial(
        Environment $twig,
        string $layoutName,
        string $colorScheme,
        bool $useJs
    ) {
        $renderPass = $this->adaptiveResponseService->renderPass;

        $renderPass->layoutRenderNode->colorScheme = $colorScheme;

        $this->layoutInit(
            $twig,
            $renderPass->layoutRenderNode,
            $layoutName,
            $useJs
        );
    }

    /**
     * @throws Exception
     */
    public function layoutInit(
        Environment $twig,
        AbstractLayoutRenderNode $layoutRenderNode,
        string $layoutName,
        bool $useJs
    ) {
        $layoutRenderNode->name = $layoutName;
        $layoutRenderNode->useJs = $useJs;
        $backEndAssets = $layoutRenderNode->assets;

        // No layout assets on ajax responses.
        $layoutRenderNode->hasAssets = $this
            ->adaptiveResponseService
            ->renderPass
            ->adaptiveResponse
            ->isHtmlRequest();

        $this->initRenderNode(
            $layoutRenderNode,
            $layoutName,
            $useJs
        );

        $this->translator->setDomainFromPath(
            $layoutRenderNode->getContextType(),
            $layoutName
        );

        // No main js found.
        if (empty($layoutRenderNode->assets[Asset::EXTENSION_JS])) {
            // Try to load default js file.
            // Do not preload JS as it is configured
            // to wait for dom content loaded anyway.
            $this->assetsService->assetsDetectForType(
                'layouts/default/layout',
                Asset::EXTENSION_JS,
                $layoutRenderNode,
                true
            );
        }

        $layoutRenderNode->assets[Asset::EXTENSION_JS] = array_merge(
            $layoutRenderNode->assets[Asset::EXTENSION_JS],
            $backEndAssets[Asset::EXTENSION_JS]
        );

        $layoutRenderNode->assets[Asset::EXTENSION_CSS] = array_merge(
            $layoutRenderNode->assets[Asset::EXTENSION_CSS],
            $backEndAssets[Asset::EXTENSION_CSS]
        );

        $this->adaptiveResponseService->renderPass->setCurrentContextRenderNode(
            $layoutRenderNode
        );

        if (AdaptiveResponse::BASE_MODAL === $this->adaptiveResponseService->getResponse()->getRenderingBase()) {
            $this->componentService->componentInitLayout(
                $twig,
                ComponentService::COMPONENT_NAME_MODAL,
                [
                    'adaptiveResponseBodyDestination' => true,
                ]
            );
        }
    }
}
