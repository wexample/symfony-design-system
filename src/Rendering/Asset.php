<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Wexample\Helpers\Helper\TextHelper;
use Wexample\Helpers\Traits\WithDomId;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\WebRenderNode\Rendering\Traits\WithView;

class Asset extends \Wexample\WebRenderNode\Asset\Asset
{
    use WithDomId;
    use WithView;

    private function buildView(string $path): string
    {
        $path = TextHelper::trimFirstChunk(
            FileHelper::removeExtension($path),
            AssetsService::DIR_BUILD
        );

        $explode = explode('/', $path);
        $parts = array_slice($explode, 2);
        array_unshift($parts, current($explode));

        return implode('/', $parts);
    }

    public function toArray(): array
    {
        return parent::toArray()
            + [
                'view' => $this->view,
            ];
    }
}
