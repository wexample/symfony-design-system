<?php

namespace Wexample\SymfonyDesignSystem\Form\Demo;

use Symfony\Component\OptionsResolver\OptionsResolver;

class FormSubmitBehaviorAjaxDemoForm extends FormSubmitBehaviorDemoForm
{
    public static bool $ajax = true;

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'translation_domain' => 'WexampleSymfonyDesignSystemBundle.forms.demo.form_submit_behavior_ajax_demo_form',
        ]);
    }
}
