<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wexample\SymfonyDesignSystem\Class\UploadRules;
use Wexample\SymfonyDesignSystem\Service\ChunkedUploadReceiver;
use Wexample\SymfonyDesignSystem\Service\UploadTokenService;

/**
 * Where a browser sends the pieces of a file, at the address a page signed
 * for one directory (`upload_url()`). Any kind of file, unless the page said
 * otherwise in the same signature (`accept`, `max_size`): what a file is for
 * is the business of whatever reads the directory, what it may be is the
 * page's.
 */
#[Route(path: '/_upload/', name: 'wexample_design_system_upload_')]
class UploadController extends AbstractController
{
    private const string TRANSLATION_PREFIX = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.upload.';

    #[Route(path: '{token}', name: 'chunk', methods: [Request::METHOD_POST])]
    public function chunk(
        string $token,
        Request $request,
        UploadTokenService $tokens,
        ChunkedUploadReceiver $receiver,
        TranslatorInterface $translator,
    ): JsonResponse {
        $directory = $tokens->directory($token);

        if (null === $directory) {
            return new JsonResponse(['error' => 'This upload address is not valid, or no longer.'], 403);
        }

        $chunk = $request->files->get('chunk');

        if (! $chunk instanceof UploadedFile || ! $chunk->isValid()) {
            return new JsonResponse(['error' => 'No piece of file came with the request.'], 400);
        }

        $rules = $tokens->rules($token);
        $fileName = $request->request->getString('file_name');
        $total = $request->request->getInt('total');

        // Refused on its first piece, from what it says of itself: nothing
        // more of it is sent, nothing of it is kept.
        if (! $rules->allowsSize($total)) {
            return $this->refuse($translator, 'refused_size', $fileName, $rules, 413);
        }

        if (false === $rules->allowsName($fileName)) {
            return $this->refuse($translator, 'refused_type', $fileName, $rules, 415);
        }

        try {
            $path = $receiver->receive(
                $directory,
                $request->request->getString('upload_id'),
                $fileName,
                $request->request->getInt('offset'),
                $total,
                $chunk,
            );
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 400);
        }

        if (null === $path) {
            return new JsonResponse(['complete' => false]);
        }

        // A name saying nothing of an accepted kind: the content says it, once
        // whole, and a file of another kind leaves the directory at once.
        if (null === $rules->allowsName($fileName) && ! $rules->allowsMimeType(mime_content_type($path) ?: null)) {
            unlink($path);

            return $this->refuse($translator, 'refused_type', $fileName, $rules, 415);
        }

        return new JsonResponse(['complete' => true, 'name' => basename($path)]);
    }

    private function refuse(
        TranslatorInterface $translator,
        string $reason,
        string $fileName,
        UploadRules $rules,
        int $status,
    ): JsonResponse {
        return new JsonResponse(['error' => $translator->trans(self::TRANSLATION_PREFIX.$reason, [
            '%name%' => basename(str_replace('\\', '/', $fileName)),
            '%accept%' => implode(', ', $rules->accept),
            '%max%' => $this->formatSize((int) $rules->maxSize),
        ])], $status);
    }

    // A size as a person reads it: the largest unit it reaches, one decimal.
    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;
        $size = (float) $bytes;

        while ($size >= 1024 && $index < count($units) - 1) {
            $size /= 1024;
            ++$index;
        }

        return round($size, 1).' '.$units[$index];
    }
}
