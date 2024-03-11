<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wexample\SymfonyDesignSystem\Controller\AbstractController;
use Wexample\SymfonyDesignSystem\Helper\DesignSystemHelper;
use Wexample\SymfonyDesignSystem\Helper\TemplateHelper;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyHelpers\Helper\VariableHelper;
use function in_array;
use function is_null;

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

    private string $body;

    private string $outputType;

    private string $renderingBase;

    private array $parameters = [];

    private RenderPass $renderPass;

    public function isJsonRequest(): bool
    {
        return self::OUTPUT_TYPE_RESPONSE_JSON === $this->getOutputType();
    }

    public function setView(
        string $view,
        $parameters = null
    ): self {
        $this->view = $view;

        if ($parameters) {
            $this->setParameters($parameters);
        }

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body = null): AdaptiveResponse
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function render(): Response
    {
        if (self::OUTPUT_TYPE_RESPONSE_JSON === $this->getOutputType()) {
            return $this->renderJson();
        }

        return $this->renderHtml();
    }

    /**
     * @throws Exception
     */
    public function renderHtml(): Response
    {
        return $this->renderResponse();
    }

    /**
     * @throws Exception
     */
    public function renderJson(): JsonResponse
    {
        $this->renderPass->layoutRenderNode->page->body = $this->renderResponse()->getContent();

        $response = new JsonResponse(
            $this->renderPass->layoutRenderNode->toRenderData()
        );

        // Prevents browser to display json response when
        // clicking on back button.
        $response->headers->set('Vary', 'Accept');

        return $response;
    }

    /**
     * @throws Exception
     */
    public function renderResponse(): Response
    {
        $view = $this->getView();

        if (!$view) {
            throw new Exception('View must be defined before adaptive rendering');
        }

        return $this->controller->adaptiveRender(
            $view,
            $this->getParameters() + [
                AdaptiveResponse::RENDER_PARAM_NAME_OUTPUT_TYPE => $this->detectOutputType(),
                AdaptiveResponse::RENDER_PARAM_NAME_BASE => $this->detectOutputType(),
            ]
        );
    }
}
