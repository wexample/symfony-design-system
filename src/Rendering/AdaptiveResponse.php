<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Symfony\Component\HttpFoundation\Request;
use Wexample\SymfonyDesignSystem\Controller\AbstractController;
use Wexample\SymfonyDesignSystem\Helper\DesignSystemHelper;
use Wexample\SymfonyDesignSystem\Helper\TemplateHelper;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

class AdaptiveResponse
{
    public const BASE_MODAL = VariableHelper::MODAL;

    public const BASE_PAGE = VariableHelper::PAGE;

    public const BASE_DEFAULT = VariableHelper::DEFAULT;

    public const BASES_MAIN_DIR = DesignSystemHelper::FOLDER_FRONT_ALIAS.'bases/';

    public const OUTPUT_TYPE_RESPONSE_HTML = VariableHelper::HTML;

    public const OUTPUT_TYPE_RESPONSE_JSON = VariableHelper::JSON;

    public const RENDER_PARAM_NAME_BASE = 'adaptive_base';

    public const RENDER_PARAM_NAME_OUTPUT_TYPE = 'adaptive_output_type';

    private string $outputType;

    private string $renderingBase;

    protected array $allowedBases = [
        self::BASE_MODAL,
        self::BASE_PAGE,
        self::BASE_DEFAULT,
    ];

    public function __construct(
        protected Request $request,
        protected AbstractController $controller,
        protected AdaptiveResponseService $adaptiveResponseService,
    ) {
        $this->setOutputType(
            $this->detectOutputType()
        );
        $this->setRenderingBase(
            $this->detectRenderingBase()
        );
    }

    public function setOutputType(string $type): self
    {
        $this->outputType = $type;

        return $this;
    }

    public function getRenderingBase(): string
    {
        return $this->renderingBase;
    }

    public function setRenderingBase(string $base): self
    {
        $this->renderingBase = $base;

        return $this;
    }

    public function getRenderingBasePath(array $twigContext): string
    {
        return self::BASES_MAIN_DIR
            .$this->getOutputType($twigContext)
            .FileHelper::FOLDER_SEPARATOR
            .$this->getRenderingBase()
            .TemplateHelper::TEMPLATE_FILE_EXTENSION;
    }

    /**
     * Return detected output type if not overridden in twig.
     */
    public function getOutputType(array $twigContext = []): string
    {
        return $twigContext[self::RENDER_PARAM_NAME_OUTPUT_TYPE]
            ?? $this->outputType;
    }

    public function isJsonRequest(): bool
    {
        return self::OUTPUT_TYPE_RESPONSE_JSON === $this->getOutputType();
    }

    public function detectRenderingBase(): string
    {
        // Allow defining json layout expected type from query string.
        $layout = $this->request->get(self::RENDER_PARAM_NAME_BASE);

        // Layout not specified in query string.
        if (is_null($layout) && $this->isJsonRequest()) {
            // Use modal as default ajax layout, but might be configurable.
            $layout = self::BASE_MODAL;
        }

        if (in_array($layout, $this->allowedBases)) {
            return $layout;
        }

        return self::BASE_DEFAULT;
    }

    public function detectOutputType(): string
    {
        return $this->request->isXmlHttpRequest() ?
            self::OUTPUT_TYPE_RESPONSE_JSON : self::OUTPUT_TYPE_RESPONSE_HTML;
    }
}
