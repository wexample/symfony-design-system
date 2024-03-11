<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Wexample\SymfonyDesignSystem\Helper\TemplateHelper;
use Wexample\SymfonyDesignSystem\Rendering\AdaptiveResponse;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyHelpers\Helper\FileHelper;

class AdaptiveResponseService
{
    protected array $allowedBases = [
        AdaptiveResponse::BASE_MODAL,
        AdaptiveResponse::BASE_PAGE,
        AdaptiveResponse::BASE_DEFAULT,
    ];

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function detectOutputType(): string
    {
        return $this->requestStack->getCurrentRequest()->isXmlHttpRequest() ?
            AdaptiveResponse::OUTPUT_TYPE_RESPONSE_JSON :
            AdaptiveResponse::OUTPUT_TYPE_RESPONSE_HTML;
    }

    public function detectRenderingBase(RenderPass $renderPass): string
    {
        // Allow defining json layout expected type from query string.
        $layout = $this->requestStack->getCurrentRequest()->get(AdaptiveResponse::RENDER_PARAM_NAME_BASE);

        // Layout not specified in query string.
        if (is_null($layout) && $renderPass->isJsonRequest()) {
            // Use modal as default ajax layout, but might be configurable.
            $layout = AdaptiveResponse::BASE_MODAL;
        }

        if (in_array($layout, $this->allowedBases)) {
            return $layout;
        }

        return AdaptiveResponse::BASE_DEFAULT;
    }

    public function getRenderingBasePath(RenderPass $renderPass): string
    {
        return AdaptiveResponse::BASES_MAIN_DIR
            .$renderPass->getOutputType()
            .FileHelper::FOLDER_SEPARATOR
            .$this->detectRenderingBase($renderPass)
            .TemplateHelper::TEMPLATE_FILE_EXTENSION;
    }
}
