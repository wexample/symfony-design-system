<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Helper\TemplateHelper;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyHelpers\AbstractBundle;
use Wexample\SymfonyHelpers\Helper\BundleHelper;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

abstract class AbstractPagesController extends AbstractController
{
    protected string $viewPathPrefix = '';

    public const NAMESPACE_CONTROLLER = 'App\\Controller\\';

    public const NAMESPACE_PAGES = self::NAMESPACE_CONTROLLER.'Pages\\';

    public const RESOURCES_DIR_PAGE = VariableHelper::PLURAL_PAGE.FileHelper::FOLDER_SEPARATOR;

    public const BUNDLE_TEMPLATE_SEPARATOR = '::';

    public function __construct(
        protected AdaptiveResponseService $adaptiveResponseService,
        protected AssetsService $assetsService,
        protected Environment $twigEnvironment,
        protected RequestStack $requestStack
    ) {
        parent::__construct(
            $adaptiveResponseService,
            $assetsService,
            $twigEnvironment
        );

        $mainRequest = $this->requestStack->getMainRequest();

        $this->requestUri = $mainRequest->getRequestUri();
    }

    public function buildTemplatePath(
        string $view,
        AbstractBundle|string|null $bundleClass = null
    ): string {
        $base = self::RESOURCES_DIR_PAGE;

        if (str_contains($view, self::BUNDLE_TEMPLATE_SEPARATOR)) {
            $exp = explode(self::BUNDLE_TEMPLATE_SEPARATOR, $view);
            $base = $exp[0].FileHelper::FOLDER_SEPARATOR.BundleHelper::BUNDLE_PATH_TEMPLATES.$base;
            $view = $exp[1];
        }

        return '@'.($bundleClass ? $bundleClass::getAlias() : 'front').'/'
            .$base.$this->viewPathPrefix.$view.TemplateHelper::TEMPLATE_FILE_EXTENSION;
    }

    protected function render(
        string $view,
        array $parameters = [],
        Response $response = null
    ): Response {
        if (!is_null($this->requestStack->getMainRequest()->get('no-js'))) {
            $this->enableJavascript = false;
        }

        return parent::render(
            $view,
            $parameters,
            $response
        );
    }

    protected function renderPage(
        string $view,
        array $parameters = [],
        Response $response = null,
        AbstractBundle|string $bundle = null
    ): Response {
        return $this->adaptiveRender(
            $this->buildTemplatePath($view, $bundle),
            $parameters,
            $response
        );
    }
}
