<?php

namespace Wexample\SymfonyDesignSystem\Service\RenderNodeUsage;

use Wexample\SymfonyDesignSystem\Rendering\RenderNode\AbstractRenderNode;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyHelpers\Helper\PathHelper;

class DefaultAssetUsageService extends AbstractAssetUsageService
{
    const NAME = 'default';

    public function buildAssetsPathsForRenderNodeAndType(
        AbstractRenderNode $renderNode,
        string $ext
    ): array {
        return [
            $this->buildBuiltPublicAssetPath($renderNode, $ext)
        ];
    }

    public function buildBuiltPublicAssetPath(AbstractRenderNode $renderNode, string $ext): string
    {
        $nameParts = explode('::', $renderNode->name);

        return AssetsService::DIR_BUILD . PathHelper::join([$nameParts[0], $ext, $nameParts[1].'.'.$ext]);
    }
}