<?php

namespace Wexample\SymfonyDesignSystem\Controller\Pages\DesignSystem\Generic;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyDesignSystem\Controller\Pages\DesignSystem\AbstractDesignSystemGenericController;
use Wexample\SymfonyDesignSystem\Service\FormProcessor\Demo\FormSubmitBehaviorAjaxDemoFormProcessor;
use Wexample\SymfonyDesignSystem\Service\FormProcessor\Demo\FormSubmitBehaviorDemoFormProcessor;
use Wexample\SymfonyForms\Attribute\FormProcessor;
use Wexample\SymfonyLoader\Controller\Pages\AbstractDesignSystemController;
use Wexample\SymfonyRouting\Attribute\TemplateBasedRoutes;

#[Route(
    name: 'wexample_design_system_generic_form_',
    path: AbstractDesignSystemController::CONTROLLER_BASE_ROUTE . '/generic/form/',
)]
#[TemplateBasedRoutes]
final class FormController extends AbstractDesignSystemGenericController
{
    // Template-based routes for index and vue are auto-generated.

    #[Route(name: 'rendered', path: 'rendered')]
    #[FormProcessor(
        processorClass: FormSubmitBehaviorDemoFormProcessor::class,
        formArgumentName: 'form_submit_behavior_demo'
    )]
    public function rendered(
        FormInterface $form_submit_behavior_demo
    ): Response {
        return $this->renderPage('rendered', [
            'form_submit_behavior_demo' => $form_submit_behavior_demo->createView(),
            'form_submit_behavior_submitted' => $form_submit_behavior_demo->isSubmitted() && $form_submit_behavior_demo->isValid(),
        ]);
    }

    #[Route(name: 'ajax', path: 'ajax')]
    #[FormProcessor(
        processorClass: FormSubmitBehaviorAjaxDemoFormProcessor::class,
        formArgumentName: 'form_submit_behavior_demo'
    )]
    public function ajax(
        FormInterface $form_submit_behavior_demo
    ): Response {
        return $this->renderPage('ajax', [
            'form_submit_behavior_demo' => $form_submit_behavior_demo->createView(),
            'form_submit_behavior_submitted' => $form_submit_behavior_demo->isSubmitted() && $form_submit_behavior_demo->isValid(),
        ]);
    }
}
