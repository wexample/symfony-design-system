<?php

namespace Wexample\SymfonyDesignSystem\Service\Usage;


use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\Usage\Traits\DesignSystemUsageServiceTrait;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\WebRenderNode\Rendering\RenderNode\AbstractRenderNode;
use Wexample\WebRenderNode\Usage\ResponsiveUsage;

final class ResponsiveAssetUsageService extends ResponsiveUsage
{
    use DesignSystemUsageServiceTrait;

    public function addAssetsForRenderNodeAndType(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        string $ext,
        string $view
    ): bool
    {
        $pathInfo = pathinfo($this->buildPublicAssetPathFromView($view, $ext));
        $maxWidth = null;
        $hasAsset = false;

        $breakpoints = array_reverse($renderPass->getDisplayBreakpoints());
        foreach ($breakpoints as $breakpointName => $minWidth) {
            $responsivePath = $pathInfo['dirname']
                . FileHelper::FOLDER_SEPARATOR
                . $pathInfo['filename']
                . '-' . $breakpointName
                . '.'
                . $pathInfo['extension'];

            if ($asset = $this->createAssetIfExists(
                $renderPass,
                $responsivePath,
                $view,
                $renderNode
            )) {
                $hasAsset = true;
                $asset->addUsageValue($this->getName(), $breakpointName);
                $asset->setMedia('screen and (min-width:' . $minWidth . 'px)' .
                    ($maxWidth ? ' and (max-width:' . $maxWidth . 'px)' : ''));
            }

            $maxWidth = $minWidth;
        }

        return $hasAsset;
    }

    public function assetNeedsInitialRender(
        Asset $asset,
        RenderPass $renderPass,
    ): bool
    {
        if ($asset->getType() === Asset::EXTENSION_CSS) {
            if (isset($asset->getUsages()[$this->getName()])) {
                // Responsive CSS are loaded in page when JS is disabled.
                return !$renderPass->isUseJs();
            }
        }

        return true;
    }
}
