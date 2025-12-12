<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Wexample\Helpers\Helper\TextHelper;
use Wexample\Helpers\Traits\WithDomId;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyTemplate\Helper\DomHelper;
use Wexample\WebRenderNode\Rendering\Traits\WithView;

class Asset extends \Wexample\WebRenderNode\Asset\Asset
{
    use WithDomId;
    use WithView;

    public function __construct(
        string $pathInManifest,
        string $view,
        protected string $usage,
        protected string $context
    )
    {
        parent::__construct(
            $pathInManifest,
            $usage,
            $context
        );

        // Same as render node id
        $this->setView($view);

        $this->setDomId(
            $this->type.'-'.DomHelper::buildStringIdentifier($this->getView())
        );
    }

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
