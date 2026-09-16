<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyLoader\Controller\Pages\AbstractDesignSystemController;

#[Route(
    name: 'wexample_design_system_fixtures_',
    path: AbstractDesignSystemController::CONTROLLER_BASE_ROUTE_INTERNAL . '/fixtures/',
)]
class FixturesController extends AbstractController
{
    private string $assetsDir = __DIR__ . '/../../assets/fixtures/';

    #[Route('placeholder.svg', name: 'placeholder_svg')]
    public function placeholderSvg(): Response
    {
        return new Response(
            file_get_contents($this->assetsDir . 'placeholder.svg'),
            Response::HTTP_OK,
            [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'public, max-age=86400',
            ]
        );
    }

    #[Route('placeholder.pdf', name: 'placeholder_pdf')]
    public function placeholderPdf(): Response
    {
        return new Response(
            file_get_contents($this->assetsDir . 'placeholder.pdf'),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'public, max-age=86400',
            ]
        );
    }
}
