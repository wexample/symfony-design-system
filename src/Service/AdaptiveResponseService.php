<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Wexample\SymfonyDesignSystem\Helper\DesignSystemHelper;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyTemplate\Helper\TemplateHelper;

class AdaptiveResponseService
{
    public const BASES_MAIN_DIR = DesignSystemHelper::FOLDER_FRONT_ALIAS . 'bases/';

    public function detectLayoutBase(RenderPass $renderPass): string
    {
        return RenderPass::BASE_DEFAULT;
    }

    public function getLayoutBasePath(RenderPass $renderPass): string
    {
        return self::BASES_MAIN_DIR
            . $renderPass->getOutputType()
            . FileHelper::FOLDER_SEPARATOR
            . $renderPass->getLayoutBase()
            . TemplateHelper::TEMPLATE_FILE_EXTENSION;
    }
}
