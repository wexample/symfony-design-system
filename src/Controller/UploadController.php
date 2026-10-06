<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyDesignSystem\Service\ChunkedUploadReceiver;
use Wexample\SymfonyDesignSystem\Service\UploadTokenService;

/**
 * Where a browser sends the pieces of a file, at the address a page signed
 * for one directory (`upload_url()`). Any kind of file: what it is for is the
 * business of whatever reads the directory, not of the way in.
 */
#[Route(path: '/_upload/', name: 'wexample_design_system_upload_')]
class UploadController extends AbstractController
{
    #[Route(path: '{token}', name: 'chunk', methods: [Request::METHOD_POST])]
    public function chunk(
        string $token,
        Request $request,
        UploadTokenService $tokens,
        ChunkedUploadReceiver $receiver,
    ): JsonResponse {
        $directory = $tokens->directory($token);

        if (null === $directory) {
            return new JsonResponse(['error' => 'This upload address is not valid, or no longer.'], 403);
        }

        $chunk = $request->files->get('chunk');

        if (! $chunk instanceof UploadedFile || ! $chunk->isValid()) {
            return new JsonResponse(['error' => 'No piece of file came with the request.'], 400);
        }

        try {
            $path = $receiver->receive(
                $directory,
                $request->request->getString('upload_id'),
                $request->request->getString('file_name'),
                $request->request->getInt('offset'),
                $request->request->getInt('total'),
                $chunk,
            );
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 400);
        }

        return new JsonResponse(null === $path
            ? ['complete' => false]
            : ['complete' => true, 'name' => basename($path)]);
    }
}
