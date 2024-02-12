<?php

namespace Wexample\SymfonyDesignSystem\Service\RenderNodeUsage;

use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyHelpers\Helper\FileHelper;

final class ResponsiveAssetUsageService extends AbstractAssetUsageService
{
    public static function getName(): string
    {
        return 'responsive';
    }

    public function addAssetsForRenderNodeAndType(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        string $ext
    ): void {
        $pathInfo = pathinfo($this->buildBuiltPublicAssetPath($renderNode, $ext));
        $maxWidth = null;

        foreach ($renderPass->displayBreakpoints as $breakpointName => $minWidth) {
            $responsivePath = $pathInfo['dirname']
                .FileHelper::FOLDER_SEPARATOR
                .$pathInfo['filename']
                .'-'.$breakpointName
                .'.'
                .$pathInfo['extension'];

            if ($asset = $this->createAssetIfExists(
                $responsivePath,
                $renderNode
            )) {
                $asset->responsive = $breakpointName;
                $asset->media = 'screen and (min-width:'.$minWidth.'px)'.
                    ($maxWidth ? ' and (max-width:'.$maxWidth.'px)' : '');
            }

            $maxWidth = $minWidth;
        }
    }

    public function isAssetReadyForServerSideRendering(
        Asset $asset,
        RenderPass $renderPass,
    ): bool {
        if ($asset->type === Asset::EXTENSION_CSS) {
            if ($asset->responsive) {
                // Responsive CSS are loaded in page when JS is disabled.
                return !$renderPass->useJs;
            }
        }
        return true;
    }
}