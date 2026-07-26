<?php

namespace Wexample\SymfonyDesignSystem\Form\Demo;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wexample\SymfonyForms\Form\AbstractForm;
use Wexample\SymfonyForms\Form\Type\DateInputType;
use Wexample\SymfonyForms\Form\Type\DatetimeInputType;
use Wexample\SymfonyForms\Form\Type\EmailInputType;
use Wexample\SymfonyForms\Form\Type\EmojiPickerType;
use Wexample\SymfonyForms\Form\Type\FileInputType;
use Wexample\SymfonyForms\Form\Type\PasswordInputType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Wexample\SymfonyForms\Form\Type\NumberInputType;
use Wexample\SymfonyForms\Form\Type\TimeInputType;
use Wexample\SymfonyForms\Form\Type\UrlInputType;
use Wexample\SymfonyForms\Form\Type\RadioInputType;
use Wexample\SymfonyForms\Form\Type\ButtonInputType;
use Wexample\SymfonyForms\Form\Type\SubmitInputType;
use Wexample\SymfonyForms\Form\Type\SwitchInputType;
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

        $builder
            ->add(
                'hidden_demo',
                HiddenType::class,
                [
                    self::FIELD_OPTION_NAME_MAPPED => false,
                    'data' => 'hidden_value',
                ]
            )
            ->add(
                'password',
                PasswordInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => false,
                    self::FIELD_OPTION_NAME_MAPPED => false,
                    'help' => true,
                    'data' => 'demo-password',
                ]
            )
            ->add(
                'emoji',
                EmojiPickerType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => true,
                    self::FIELD_OPTION_NAME_MAPPED => false,
                    'help' => true,
                    'data' => '😊',
                ]
            )
            ->add(
                'text_simple',
                TextInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => true,
                    self::FIELD_OPTION_NAME_MAPPED => false,
                    'help' => true,
                    'data' => 'Demo text',
                ]
            )
            ->add(
                'text_area',
                TextareaInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => true,
                    self::FIELD_OPTION_NAME_MAPPED => false,
                    'rows' => 4,
                    'max_rows' => 12,
                    'auto_resize' => true,
                    'help' => true,
                    'data' => 'Demo long text for the textarea field.',
                    'attr' => [
                        'placeholder' => true,
                    ],
                ]
            )
            ->add(
                'radio_choice',
                RadioInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => true,
                    self::FIELD_OPTION_NAME_MAPPED => false,
                    'choices' => ['option_a', 'option_b', 'option_c'],
                    'help' => true,
                    'data' => 'option_a',
                ]
            )
            ->add(
                'switch',
                SwitchInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => true,
                    self::FIELD_OPTION_NAME_MAPPED => false,
                    'help' => true,
                    'data' => true,
                ]
            )
            ->add(
                'date',
                DateInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => true,
                    self::FIELD_OPTION_NAME_MAPPED => false,
                    'help' => true,
                    'data' => new \DateTime('2000-01-15'),
                ]
            )
            ->add(
                'datetime',
                DatetimeInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => true,
                    self::FIELD_OPTION_NAME_MAPPED => false,
                    'help' => true,
                    'data' => new \DateTime('2000-01-15 09:00:00'),
                ]
            )
            ->add(
                'time',
                TimeInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => true,
                    self::FIELD_OPTION_NAME_MAPPED => false,
                    'help' => true,
                    'data' => new \DateTime('1970-01-01 09:00:00'),
                ]
            )
            ->add(
                'file',
                FileInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => false,
                    self::FIELD_OPTION_NAME_MAPPED => false,
                    'help' => true,
                ]
            )
            ->add(
                'email',
                EmailInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => true,
                    self::FIELD_OPTION_NAME_MAPPED => false,
                    'help' => true,
                    'data' => 'demo@example.com',
                    'attr' => ['placeholder' => true],
                ]
            )
            ->add(
                'number',
                NumberInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => true,
                    self::FIELD_OPTION_NAME_MAPPED => false,
                    'help' => true,
                    'data' => 42,
                ]
            )
            ->add(
                'url',
                UrlInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => true,
                    self::FIELD_OPTION_NAME_MAPPED => false,
                    'help' => true,
                    'data' => 'https://example.com',
                    'attr' => ['placeholder' => true],
                ]
            )
            ->add(
                'button_demo',
                ButtonInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => 'action.button_demo',
                    self::FIELD_OPTION_NAME_MAPPED => false,
                ]
            )
        ;

        $builder
            ->add('submit_error', SubmitInputType::class, [self::FIELD_OPTION_NAME_LABEL => 'action.submit_error'])
            ->add('submit_js', SubmitInputType::class, [self::FIELD_OPTION_NAME_LABEL => 'action.submit_js'])
            ->add('submit_redirect', SubmitInputType::class, [self::FIELD_OPTION_NAME_LABEL => 'action.submit_redirect'])
            ->add('submit_default', SubmitInputType::class, [self::FIELD_OPTION_NAME_LABEL => 'action.submit_default'])
        ;

        if ($isEmbedded) {
            $builder
                ->add('submit_embed_stay', SubmitInputType::class, [self::FIELD_OPTION_NAME_LABEL => 'action.submit_embed_stay'])
                ->add('submit_embed_redirect', SubmitInputType::class, [self::FIELD_OPTION_NAME_LABEL => 'action.submit_embed_redirect'])
            ;
        }
    }
}
