<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

/**
 * Fixture for the save_path check (release checklist F3, CONTRACT.md §7):
 * the hub's save_path
 * command only ever returns a path the person chose in the native dialog; it
 * never writes to it. This route is the "the app's PHP creates it" half the
 * contract talks about — hand-written and deliberately crude, no path
 * confinement, exactly like DurableUploadController.
 */
final class SavePathController extends AbstractController
{
    private const string FIXED_CONTENT = "Written via save_path.\n";

    #[Route('/save-path-write', name: 'save_path_write', methods: ['POST'])]
    #[IsCsrfTokenValid('save_path_write', tokenKey: '_csrf_token')]
    public function write(Request $request): JsonResponse
    {
        $path = $request->request->get('path');
        if (!$path) {
            return $this->json(['error' => 'no path received'], 400);
        }

        if (false === file_put_contents($path, self::FIXED_CONTENT)) {
            return $this->json(['error' => 'write failed'], 500);
        }

        return $this->json(['path' => $path]);
    }
}
