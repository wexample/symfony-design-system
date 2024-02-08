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

    public function initRenderNode(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        string $path,
    ): void {
        $renderNode->init(
            $renderPass,
            $this->buildNodeNameFromPath($path)
        );

        if ($renderNode->hasAssets) {
            $this->assetsService->assetsDetect(
                $renderNode,
                $renderNode->assets
            );
        }
    }

    private function buildNodeNameFromPath(string $renderNodePath): string
    {
        if (str_ends_with($renderNodePath, TemplateHelper::TEMPLATE_FILE_EXTENSION)) {
            $renderNodePath = substr(
                $renderNodePath,
                0,
                -strlen(TemplateHelper::TEMPLATE_FILE_EXTENSION)
            );
        }

        $layoutNameParts = explode('/', $renderNodePath);
        $bundleName = ltrim(current($layoutNameParts), '@');
        array_shift($layoutNameParts);
        $bundles = $this->kernel->getBundles();

        $nameRight = '::'.implode('/', $layoutNameParts);

        // This is a bundle alias.
        if (isset($bundles[$bundleName])) {
            $bundle = $this->kernel->getBundle($bundleName);
            return BundleHelper::getBundleCssAlias($bundle::class).$nameRight;
        }

        return 'app' . $nameRight;
    }
}
