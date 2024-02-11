<?php

namespace Wexample\SymfonyDesignSystem\Service\RenderNodeUsage;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Service\AssetsRegistryService;
use Wexample\SymfonyHelpers\Helper\FileHelper;

class ResponsiveAssetUsageService extends AbstractAssetUsageService
{
    const NAME = 'responsive';

    private array $breakpoints;

    public function __construct(
        AssetsRegistryService $assetsRegistryService,
        ParameterBagInterface $parameterBag
    ) {
        parent::__construct($assetsRegistryService);

        $this->breakpoints = array_reverse($parameterBag->get('design_system.display_breakpoints'));
    }

    public function addAssetsForRenderNodeAndType(
        AbstractRenderNode $renderNode,
        string $ext
    ): void {
        $pathInfo = pathinfo($this->buildBuiltPublicAssetPath($renderNode, $ext));
        $maxWidth = null;

        foreach ($this->breakpoints as $breakpointName => $minWidth) {
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
}