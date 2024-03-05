<?php

namespace Wexample\SymfonyDesignSystem\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Service\RenderPassBagService;

class AssetsEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        readonly private Environment $twig,
        readonly protected RenderPassBagService $renderPassBagService
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
        ];
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $renderPass = $this->renderPassBagService->getRenderPass();

        // Support regular controllers
        if ($renderPass) {
            $assetsIncludes = $this->twig->render(
                '@WexampleSymfonyDesignSystemBundle/macros/assets.html.twig',
                [
                    'render_pass' => $renderPass,
                ]
            );

            $response = $event->getResponse();
            $content = str_replace(
                RenderingHelper::PLACEHOLDER_PRELOAD_TAG,
                $assetsIncludes,
                $response->getContent()
            );

            $response->setContent($content);
        }
    }
}