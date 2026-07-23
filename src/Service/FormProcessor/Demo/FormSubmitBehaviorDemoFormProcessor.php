<?php

namespace Wexample\SymfonyDesignSystem\Service\FormProcessor\Demo;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Wexample\SymfonyDesignSystem\Form\Demo\FormSubmitBehaviorDemoForm;
use Wexample\SymfonyForms\Service\FormProcessor\AbstractFormProcessor;
use Wexample\SymfonyHelpers\Helper\RoleHelper;

class FormSubmitBehaviorDemoFormProcessor extends AbstractFormProcessor
{
    public static function getFormClass(): string
    {
        return FormSubmitBehaviorDemoForm::class;
    }

    public function getRequiredRoles(): array
    {
        return [RoleHelper::PUBLIC_ACCESS];
    }

    public function onValid(FormInterface $form): void
    {
        $behavior = $form->has('behavior')
            ? $form->get('behavior')->getData()
            : null;

        switch ($behavior) {
            case 'error':
                $this->addFormErrorFromApiKey($form, 'ERR_FORM_TEST');
                $form->get('text_simple')->addError(new FormError('@form::field.text_simple.error.ERR_FIELD_TEXT_SIMPLE_INVALID'));
                $form->get('text_area')->addError(new FormError('@form::field.text_area.error.ERR_FIELD_TEXT_AREA_INVALID'));
                $form->get('behavior')->addError(new FormError('@form::field.behavior.error.ERR_FIELD_BEHAVIOR_INVALID'));

                break;
            case self::ACTION_REDIRECT:
                $this->setSuccessAction([
                    'type' => self::ACTION_REDIRECT,
                    'url' => '/',
                ]);

                break;
            case self::ACTION_EMBED_STAY:
                $this->setSuccessAction([
                    'type' => self::ACTION_EMBED_STAY,
                ]);

                break;
            case self::ACTION_EMBED_REDIRECT:
                $this->setSuccessAction([
                    'type' => self::ACTION_EMBED_REDIRECT,
                    'url' => '/',
                ]);

                break;
            default:
                $this->setSuccessAction([
                    'type' => self::ACTION_DEFAULT,
                ]);

                break;
        }
    }

    public function handleSubmissionResponseFromForm(FormInterface $form): ?Response
    {
        $action = $this->getSuccessAction();
        if (is_array($action) && ($action['type'] ?? null) === self::ACTION_EMBED_STAY) {
            return null;
        }

        return parent::handleSubmissionResponseFromForm($form);
    }
}
