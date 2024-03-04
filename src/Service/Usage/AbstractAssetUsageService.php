<?php

namespace Wexample\SymfonyDesignSystem\Service\Usage;

use Exception;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AssetsRegistryService;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyHelpers\Helper\PathHelper;
use Wexample\SymfonyHelpers\Helper\TextHelper;

abstract class AbstractAssetUsageService
{
    /**
     * @var array|array[]|\Wexample\SymfonyDesignSystem\Rendering\Asset[][]
     */
    protected array $assets = AssetsService::ASSETS_DEFAULT_EMPTY;

    public function __construct(
        protected AssetsRegistryService $assetsRegistryService
    ) {

    }

    abstract public static function getName(): string;

    public function buildBuiltPublicAssetPath(
        AbstractRenderNode $renderNode,
        string $ext
    ): string {
        $nameParts = explode('::', $renderNode->name);

        return AssetsRegistryService::DIR_BUILD.PathHelper::join([$nameParts[0], $ext, $nameParts[1].'.'.$ext]);
    }

    public function addAssetsForRenderNodeAndType(
        RenderPass $renderPass,
        AbstractRenderNode $renderNode,
        string $ext
    ): void {
        $pathInfo = pathinfo($this->buildBuiltPublicAssetPath($renderNode, $ext));
        $usage = $this->getName();
        $usageKebab = TextHelper::toKebab($usage);

        if (isset($renderPass->usagesList[$usage]['list'])) {
            foreach ($renderPass->usagesList[$usage]['list'] as $usageValue => $config) {
                $assetPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'.'.$usageKebab.'.'.$usageValue.'.'.$pathInfo['extension'];

                if ($asset = $this->createAssetIfExists(
                    $assetPath,
                    $renderNode
                )) {
                    $asset->usages[$usage] = $usageValue;
                }
            }
        }
    }

    protected function createAssetIfExists(
        string $pathRelativeToPublic,
        AbstractRenderNode $renderNode,
    ): ?Asset {
        if (!$this->assetsRegistryService->assetExists($pathRelativeToPublic)) {
            return null;
        }

        $realPath = $this->assetsRegistryService->getRealPath($pathRelativeToPublic);

        if (!$realPath) {
            throw new Exception('Unable to find asset "'.$pathRelativeToPublic.'" in manifest.');
        }

        $asset = new Asset(
            $pathRelativeToPublic,
            $this::getName()
        );

        $renderNode->assets[$asset->type][] = $asset;
        $this->assets[$asset->type][] = $asset;

        $this->assetsRegistryService->addAsset(
            $asset,
        );

        return $asset;
    }

    public function hasAsset(?string $type = null): bool
    {
        if ($type) {
            return !empty($this->assets[$type]);
        }

        foreach ($this->assets as $type => $assets) {
            if ($this->hasAsset($type)) {
                return true;
            }
        }

        return false;
    }

    public function assetNeedsInitialRender(
        Asset $asset,
        RenderPass $renderPass,
    ): bool {
        $usage = $this->getName();

        // There is more than one same usage in frontend.
        return $this->hasExtraSwitchableUsage($renderPass)
            // This is the base usage (i.e. default).
            || $asset->usages[$usage] == $renderPass->getUsageConfig($usage, 'default');
    }

    protected function hasExtraSwitchableUsage(RenderPass $renderPass): bool
    {
        $usage = static::getName();

        foreach ($renderPass->usagesList[$usage] as $scheme => $config) {
            // There is at least one other switchable usage different from default one.
            if (($config['allow_switch'] ?? false)
                && $scheme !== $renderPass->getUsageConfig($usage, 'default')) {
                return true;
            }
        }

        return false;
    }

    public function getServerSideRenderedAssets(
        RenderPass $renderPass,
        string $type
    ): array {
        if ($this->hasExtraSwitchableUsage($renderPass)) {
            return [];
        }

        $output = [];
        foreach ($this->assets[$type] as $asset) {
            if ($asset->isServerSideRendered()) {
                $output[] = $asset;
            }
        }

        return $output;
    }
}