<?php

namespace Wexample\SymfonyDesignSystem\Service\Usage;

use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;

final class ColorSchemeAssetUsageService extends AbstractAssetUsageService
{
    public static function getName(): string
    {
        return 'color_scheme';
    }

    public function addAssetsForRenderNodeAndType(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        string $ext
    ): void {
        $pathInfo = pathinfo($this->buildBuiltPublicAssetPath($renderNode, $ext));

        foreach ($renderPass->colorSchemes as $colorScheme) {
            $assetPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'.color-scheme.'.$colorScheme.'.'.$pathInfo['extension'];

            if ($asset = $this->createAssetIfExists(
                $assetPath,
                $renderNode
            )) {
                $asset->colorScheme = $colorScheme;
            }
        }
    }
}