<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyHelpers\Controller\AbstractController as HelpersAbstractController;
use Wexample\SymfonyLoader\Controller\Pages\AbstractDesignSystemController;

#[Route(
    name: 'wexample_design_system_fixtures_',
    path: AbstractDesignSystemController::CONTROLLER_BASE_ROUTE_INTERNAL . '/fixtures/',
)]
class FixturesController extends AbstractController
{
    private const string ASSETS_DIR = __DIR__ . '/../../assets/fixtures/';

    #[Route('placeholder.svg', name: 'placeholder_svg')]
    public function placeholderSvg(): Response
    {
        return new Response(
            file_get_contents(self::ASSETS_DIR . 'placeholder.svg'),
            Response::HTTP_OK,
            [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'public, max-age=86400',
            ]
        );
    }

    /**
     * A picture of any size, drawn on the spot: a gradient of `hue` (0-359)
     * at width × height, for a demo needing pictures of given ratios without
     * files. Reached from a script as well (exposed).
     */
    #[Route(
        'picture/{width}x{height}.svg',
        name: 'picture_svg',
        requirements: ['width' => '\\d{1,4}', 'height' => '\\d{1,4}'],
        options: HelpersAbstractController::ROUTE_OPTIONS_ONLY_EXPOSE,
    )]
    public function pictureSvg(
        Request $request,
        int $width,
        int $height
    ): Response {
        $width = max(1, $width);
        $height = max(1, $height);
        $hue = abs($request->query->getInt('hue', 210)) % 360;
        $end = ($hue + 60) % 360;

        return new Response(
            '<svg xmlns="http://www.w3.org/2000/svg" width="'.$width.'" height="'.$height.'" viewBox="0 0 '.$width.' '.$height.'">'
            .'<defs><linearGradient id="g" x2="1" y2="1">'
            .'<stop offset="0" stop-color="hsl('.$hue.' 70% 65%)"/>'
            .'<stop offset="1" stop-color="hsl('.$end.' 60% 35%)"/>'
            .'</linearGradient></defs>'
            .'<rect width="100%" height="100%" fill="url(#g)"/></svg>',
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
            file_get_contents(self::ASSETS_DIR . 'placeholder.pdf'),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'public, max-age=86400',
            ]
        );
    }
}
