<?php

namespace Wexample\SymfonyDesignSystem\Service\FormProcessor\Demo;

use Wexample\SymfonyDesignSystem\Form\Demo\FormSubmitBehaviorAjaxDemoForm;

class FormSubmitBehaviorAjaxDemoFormProcessor extends FormSubmitBehaviorDemoFormProcessor
{
    public static function getFormClass(): string
    {
        return FormSubmitBehaviorAjaxDemoForm::class;
    }
}
