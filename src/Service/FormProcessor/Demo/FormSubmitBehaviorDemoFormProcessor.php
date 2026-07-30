<?php

namespace Wexample\SymfonyDesignSystem\Service\FormProcessor\Demo;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
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
        $behavior = match(true) {
            $form->has('submit_error') && $form->get('submit_error')->isClicked() => 'error',
            $form->has('submit_js') && $form->get('submit_js')->isClicked() => 'js',
            $form->has('submit_redirect') && $form->get('submit_redirect')->isClicked() => 'redirect',
            $form->has('submit_embed_stay') && $form->get('submit_embed_stay')->isClicked() => self::ACTION_EMBED_STAY,
            $form->has('submit_embed_redirect') && $form->get('submit_embed_redirect')->isClicked() => self::ACTION_EMBED_REDIRECT,
            default => 'default',
        };

        switch ($behavior) {
            case 'error':
                $this->addFormErrorFromApiKey($form, 'ERR_FORM_TEST');
                $form->get('password')->addError(new FormError('@form::field.password.error.ERR_FIELD_PASSWORD_INVALID'));
                $form->get('emoji')->addError(new FormError('@form::field.emoji.error.ERR_FIELD_EMOJI_INVALID'));
                $form->get('text_simple')->addError(new FormError('@form::field.text_simple.error.ERR_FIELD_TEXT_SIMPLE_INVALID'));
                $form->get('text_area')->addError(new FormError('@form::field.text_area.error.ERR_FIELD_TEXT_AREA_INVALID'));
                $form->get('number')->addError(new FormError('@form::field.number.error.ERR_FIELD_NUMBER_INVALID'));
                $form->get('email')->addError(new FormError('@form::field.email.error.ERR_FIELD_EMAIL_INVALID'));
                $form->get('url')->addError(new FormError('@form::field.url.error.ERR_FIELD_URL_INVALID'));
                $form->get('date')->addError(new FormError('@form::field.date.error.ERR_FIELD_DATE_INVALID'));
                $form->get('datetime')->addError(new FormError('@form::field.datetime.error.ERR_FIELD_DATETIME_INVALID'));
                $form->get('time')->addError(new FormError('@form::field.time.error.ERR_FIELD_TIME_INVALID'));
                $form->get('file')->addError(new FormError('@form::field.file.error.ERR_FIELD_FILE_INVALID'));
                $form->get('radio_choice')->addError(new FormError('@form::field.radio_choice.error.ERR_FIELD_RADIO_CHOICE_INVALID'));
                $form->get('switch')->addError(new FormError('@form::field.switch.error.ERR_FIELD_SWITCH_INVALID'));

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
}
