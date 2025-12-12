<?php

namespace Wexample\SymfonyDesignSystem\Rendering;


use Wexample\SymfonyDesignSystem\Helper\DesignSystemHelper;
use Wexample\SymfonyDesignSystem\Rendering\Traits\WithView;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

class RenderPass
{
    use WithView;

    public const BASE_DEFAULT = VariableHelper::DEFAULT;

    public const BASES_MAIN_DIR = DesignSystemHelper::FOLDER_FRONT_ALIAS . 'bases/';

    public const OUTPUT_TYPE_RESPONSE_HTML = VariableHelper::HTML;

    private string $outputType = self::OUTPUT_TYPE_RESPONSE_HTML;

    protected string $layoutBase = self::BASE_DEFAULT;

    public function __construct(
        string $view
    )
    {
        $this->setView($view);
    }

    public function getOutputType(): string
    {
        return $this->outputType;
    }

    public function getLayoutBase(): string
    {
        return $this->layoutBase;
    }
}
