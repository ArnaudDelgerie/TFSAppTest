<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

/**
 * Fixture for the hub's plan 049 end-to-end validation: a file written here
 * lands under APP_UPLOAD_DIR rather than anywhere under public/, so it is
 * expected to survive an update, a rollback, and an export/import round
 * trip (decision 006). Hand-written and deliberately crude on purpose — no
 * path-confining store, no hardened response class, both are TFSAppBundle's
 * own later plan — this controller is expected to be rewritten onto that
 * service once it exists.
 */
final class DurableUploadController extends AbstractController
{
    #[Route('/durable-upload', name: 'durable_upload', methods: ['POST'])]
    #[IsCsrfTokenValid('durable_upload', tokenKey: '_csrf_token')]
    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file) {
            return $this->json(['error' => 'no file received'], 400);
        }

        $uploadDir = $_SERVER['APP_UPLOAD_DIR'] ?? getenv('APP_UPLOAD_DIR') ?: null;
        if (!$uploadDir) {
            return $this->json(['error' => 'APP_UPLOAD_DIR is not set'], 500);
        }

        $filename = basename($file->getClientOriginalName());
        $file->move($uploadDir, $filename);

        return $this->json(['filename' => $filename]);
    }

    #[Route('/durable-upload/{filename}', name: 'durable_download', methods: ['GET'])]
    public function download(string $filename): Response
    {
        $uploadDir = $_SERVER['APP_UPLOAD_DIR'] ?? getenv('APP_UPLOAD_DIR') ?: null;
        $path = $uploadDir ? $uploadDir.'/'.basename($filename) : null;
        if (!$path || !is_file($path)) {
            throw $this->createNotFoundException();
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($filename));
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
