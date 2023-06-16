<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

class Asset extends RenderDataGenerator
{
    public const EXTENSION_CSS = 'css';

    public const EXTENSION_JS = 'js';

    public const EXTENSION_VUE = 'vue';
    public function toRenderData(): array
    {
        return $this->serializeVariables([
            'active',
            'colorScheme',
            'filesize',
            'id',
            'initialLayout',
            'media',
            'path',
            'preload',
            'responsive',
            'rendered',
            'type',
            'usage',
        ]);
    }
}
