<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyDesignSystem\Service\AvatarService;
use Wexample\SymfonyLoader\Controller\Pages\AbstractDesignSystemController;

/**
 * The face an engine draws for a seed, as an SVG. The same address always
 * answers the same face, so it is kept for long by whoever reads it.
 */
class AvatarController extends AbstractController
{
    final public const string ROUTE_NAME = 'wexample_design_system_avatar';

    #[Route(
        path: AbstractDesignSystemController::CONTROLLER_BASE_ROUTE_INTERNAL.'/avatar/{engine}/{seed}.svg',
        name: self::ROUTE_NAME,
        requirements: ['engine' => '[a-z0-9-]+', 'seed' => '[^/]{1,200}'],
        options: ['expose' => true],
        methods: [Request::METHOD_GET],
    )]
    public function avatar(
        string $engine,
        string $seed,
        Request $request,
        AvatarService $avatars,
    ): Response {
        if (! $avatars->hasEngine($engine)) {
            throw new NotFoundHttpException();
        }

        $label = mb_substr((string) $request->query->get('label', ''), 0, 200) ?: null;

        return new Response($avatars->render($engine, $seed, $label), Response::HTTP_OK, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
