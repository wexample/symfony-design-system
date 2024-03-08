<?php

namespace Wexample\SymfonyDesignSystem\Service\Usage;

use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;

final class DefaultAssetUsageService extends AbstractAssetUsageService
{
    public static function getName(): string
    {
        return 'default';
    }

    public function addAssetsForRenderNodeAndType(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        string $ext,
        ?string $templateName = null
    ): void {
        $this->createAssetIfExists(
            $this->buildPublicAssetPathFromTemplateName(
                $templateName ?: $renderNode->getTemplateAbstractPath(),
                $ext
            ),
            $renderNode,
        );
    }

    public function assetNeedsInitialRender(
        Asset $asset,
        RenderPass $renderPass,
    ): bool {
        if ($asset->type === Asset::EXTENSION_JS) {
            return $renderPass->isUseJs();
        }

        return true;
    }
}