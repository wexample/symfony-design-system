<?php

namespace Wexample\SymfonyDesignSystem\Service\Usage\Traits;

use Wexample\Helpers\Helper\PathHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\Traits\DesignSystemRenderNodeTrait;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\WebRenderNode\Asset\Asset;
use Wexample\WebRenderNode\Rendering\RenderNode\AbstractRenderNode;

trait DesignSystemUsageServiceTrait
{
    public function buildPublicAssetPathFromView(
        string $view,
        string $ext
    ): string
    {
        $nameParts = explode('/', $view);
        $bundle = array_shift($nameParts);

        return AssetsService::DIR_BUILD . PathHelper::join(array_merge([$bundle, $ext], $nameParts)) . '.' . $ext;
    }

    /**
     * @param RenderPass $renderPass
     * @param AbstractRenderNode|DesignSystemRenderNodeTrait $renderNode
     * @param string $ext
     * @param string $view
     * @return bool
     */
    public function addAssetsForRenderNodeAndType(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        string $ext,
        string $view
    ): bool
    {
        $path = $this->buildPublicAssetPathFromView(
            $view,
            $ext
        );

        $asset = new Asset(
            $path,
            $view,
            static::getName(),
            $renderNode->getContextType()
        );

        $renderNode->addAsset($asset);

        return true;
    }
}
