<?php

namespace Wexample\SymfonyDesignSystem\Service;


use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyTemplate\Helper\TemplateHelper;

class AdaptiveResponseService
{
    public function getLayoutBasePath(RenderPass $renderPass): string
    {
        return RenderPass::BASES_MAIN_DIR
            . $renderPass->getOutputType()
            . FileHelper::FOLDER_SEPARATOR
            . $renderPass->getLayoutBase()
            . TemplateHelper::TEMPLATE_FILE_EXTENSION;
    }
}
