<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Wexample\SymfonyDesignSystem\Helper\TemplateHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyHelpers\Helper\FileHelper;

class AdaptiveResponseService
{
    protected array $allowedBases = [
        RenderPass::BASE_MODAL,
        RenderPass::BASE_PAGE,
        RenderPass::BASE_DEFAULT,
    ];

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function detectOutputType(): string
    {
        return $this->requestStack->getCurrentRequest()->isXmlHttpRequest() ?
            RenderPass::OUTPUT_TYPE_RESPONSE_JSON :
            RenderPass::OUTPUT_TYPE_RESPONSE_HTML;
    }

    public function detectLayoutBase(RenderPass $renderPass): string
    {
        // Layout not specified in query string.
        if ($renderPass->isJsonRequest()) {
            // Use modal as default ajax layout, but might be configurable.
            return RenderPass::BASE_MODAL;
        }

        return RenderPass::BASE_DEFAULT;
    }

    public function getLayoutBasePath(RenderPass $renderPass): string
    {
        return RenderPass::BASES_MAIN_DIR
            .$renderPass->getOutputType()
            .FileHelper::FOLDER_SEPARATOR
            .$renderPass->getLayoutBase()
            .TemplateHelper::TEMPLATE_FILE_EXTENSION;
    }
}
