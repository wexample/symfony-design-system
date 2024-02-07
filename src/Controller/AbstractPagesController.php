<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Symfony\Component\HttpFoundation\Response;
use Wexample\SymfonyDesignSystem\Helper\TemplateHelper;
use Wexample\SymfonyHelpers\AbstractBundle;
use Wexample\SymfonyHelpers\Helper\BundleHelper;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

abstract class AbstractPagesController extends AbstractController
{
    protected string $viewPathPrefix = '';

    public const RESOURCES_DIR_PAGE = VariableHelper::PLURAL_PAGE.FileHelper::FOLDER_SEPARATOR;

    public const BUNDLE_TEMPLATE_SEPARATOR = '::';

    protected function buildTemplatePath(
        string $view,
        AbstractBundle|string|null $bundleClass = null
    ): string {
        $base = self::RESOURCES_DIR_PAGE;

        if (str_contains($view, self::BUNDLE_TEMPLATE_SEPARATOR)) {
            $exp = explode(self::BUNDLE_TEMPLATE_SEPARATOR, $view);
            $base = $exp[0].FileHelper::FOLDER_SEPARATOR.BundleHelper::BUNDLE_PATH_TEMPLATES.$base;
            $view = $exp[1];
        }

        return '@'
            .($bundleClass ? $bundleClass::getAlias() : 'front').'/'
            .$base.$this->viewPathPrefix.$view.TemplateHelper::TEMPLATE_FILE_EXTENSION;
    }

    protected function renderPage(
        string $view,
        array $parameters = [],
        Response $response = null,
        AbstractBundle|string $bundle = null
    ): Response {
        return $this->adaptiveRender(
            $this->buildTemplatePath($view, ($bundle ?: $this->getControllerBundle())),
            $parameters,
            $response
        );
    }
}
