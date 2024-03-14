<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Wexample\SymfonyDesignSystem\Helper\TemplateHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyHelpers\Helper\BundleHelper;

abstract class RenderNodeService
{
    public function __construct(
        protected AssetsService $assetsService,
        protected AdaptiveResponseService $adaptiveResponseService,
        protected KernelInterface $kernel,
    ) {
    }

    /**
     * Render node path or name are created after class construction,
     * as layout name is given by the template and so undefined
     * on layout render node class instanciation.
     */
    public function initRenderNode(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        string $name,
        string $useJs
    ) {
        $renderNode->init(
            $renderPass,
            $name
        );

        if ($renderNode->hasAssets) {
            $this->assetsService->assetsDetect(
                $renderNode,
                $renderNode->assets
            );

            $this->assetsService->assetsPreload(
                $renderNode->assets['css'],
                $this->adaptiveResponseService->renderPass->layoutRenderNode->colorScheme,
                $useJs,
            );
        }
    }
}
