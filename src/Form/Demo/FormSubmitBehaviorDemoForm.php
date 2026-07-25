<?php

namespace Wexample\SymfonyDesignSystem\Form\Demo;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wexample\SymfonyForms\Form\AbstractForm;
use Wexample\SymfonyForms\Form\Type\RadioInputType;
use Wexample\SymfonyForms\Form\Type\SelectInputType;
use Wexample\SymfonyForms\Form\Type\TextareaInputType;
use Wexample\SymfonyForms\Form\Type\TextInputType;
use Wexample\SymfonyLoader\Helper\AdaptiveRequestHelper;

class FormSubmitBehaviorDemoForm extends AbstractForm
{
    public static bool $ajax = false;

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'translation_domain' => 'WexampleSymfonyDesignSystemBundle.forms.demo.form_submit_behavior_demo_form',
        ]);
    }

    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $request = $this->requestStack->getCurrentRequest();
        $isEmbedded = $request ? AdaptiveRequestHelper::isEmbedded($request) : false;
        $behaviorChoices = [
            'default',
            'js',
            'error',
            'redirect',
        ];

        if ($isEmbedded) {
            $behaviorChoices[] = 'embed_stay';
            $behaviorChoices[] = 'embed_redirect';
        }

        $builder
            ->add(
                'text_simple',
                TextInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => false,
                    self::FIELD_OPTION_NAME_MAPPED => false,
                ]
            )
            ->add(
                'text_area',
                TextareaInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => false,
                    self::FIELD_OPTION_NAME_MAPPED => false,
                    'rows' => 4,
                    'max_rows' => 12,
                    'auto_resize' => true,
                    'attr' => [
                        'placeholder' => true,
                    ],
                ]
            )
            ->add(
                'behavior',
                SelectInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => 'field.behavior.label',
                    self::FIELD_OPTION_NAME_REQUIRED => true,
                    self::FIELD_OPTION_NAME_MAPPED => false,
                    'choices' => $behaviorChoices,
                    'placeholder' => false,
                ]
            )
            ->add(
                'radio_choice',
                RadioInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => false,
                    self::FIELD_OPTION_NAME_MAPPED => false,
                    'choices' => ['option_a', 'option_b', 'option_c'],
                ]
            );

        $this->builderAddSubmit($builder, 'action.submit');
    }
}
