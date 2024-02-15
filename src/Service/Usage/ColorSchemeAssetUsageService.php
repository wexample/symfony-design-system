<?php

namespace Wexample\SymfonyDesignSystem\Service\Usage;

use Wexample\SymfonyDesignSystem\Rendering\Asset;
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

        foreach ($renderPass->colorSchemes as $colorScheme => $config) {
            $assetPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'.color-scheme.'.$colorScheme.'.'.$pathInfo['extension'];

            if ($asset = $this->createAssetIfExists(
                $assetPath,
                $renderNode
            )) {
                $asset->colorScheme = $colorScheme;
            }
        }
    }

    private function hasExtraSwitchableColorSchemes(RenderPass $renderPass): bool
    {
        foreach ($renderPass->colorSchemes as $scheme => $config) {
            // There is at least one other switchable color scheme different than default one.
            if (($config['allow_switch'] ?? false)
                && $scheme != $renderPass->colorScheme) {
                return true;
            }
        }

        return false;
    }

    public function isAssetReadyForServerSideRendering(
        Asset $asset,
        RenderPass $renderPass,
    ): bool {
        // There is more than one color scheme in frontend.
        return $this->hasExtraSwitchableColorSchemes($renderPass)
            // This is the base color scheme (i.e. default).
            || $asset->colorScheme == $renderPass->colorScheme;
    }
}