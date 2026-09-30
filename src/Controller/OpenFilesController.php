<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

/**
 * Fixture for the open-files checks (release checklist F10/F11, CONTRACT.md
 * §7): the PHP-side half of
 * an open-files receiver's acceptance. The hub's contract is explicit that
 * delivery is replayable until acknowledgement, not exactly-once — a reload
 * between acceptance and acknowledgement re-exposes the same request id — so
 * acceptance must be idempotent by request id on this side too, before any
 * real work would run. Hand-written and deliberately crude, exactly like
 * SavePathController and CloseGuardController.
 *
 * One route, one JSON file under the cache directory, one answer: `accepted`
 * is true the first time a request id arrives and false forever after, which
 * is precisely the answer a receiver needs to decide "open a new document"
 * from "select the already-open one" without processing anything twice. The
 * paths ride along for the record, each classified as a file or a directory —
 * a receiver discovers what each delivered path
 * is by asking its own backend, never from the wire, and a path deleted
 * between delivery and acceptance records as "gone" rather than failing the
 * acceptance. What the app does with them is its own backend's business, and
 * here that business is writing a log line.
 */
final class OpenFilesController extends AbstractController
{
    #[Route('/open-files/accept', name: 'open_files_accept', methods: ['POST'])]
    #[IsCsrfTokenValid('open_files_probe', tokenKey: '_csrf_token')]
    public function accept(Request $request): JsonResponse
    {
        $id = $request->request->get('id');
        $paths = json_decode((string) $request->request->get('paths', '[]'), true);
        if (!$id || !\is_array($paths)) {
            return $this->json(['error' => 'no request received'], 400);
        }

        $file = $this->storePath();
        $accepted = json_decode(@file_get_contents($file) ?: '[]', true) ?: [];
        if (\array_key_exists($id, $accepted)) {
            return $this->json(['id' => $id, 'accepted' => false, 'kinds' => $accepted[$id]['kinds'], 'at' => $accepted[$id]['at']]);
        }

        $kinds = [];
        foreach ($paths as $path) {
            if (\is_string($path) && \is_file($path)) {
                $kinds[] = 'file';
            } elseif (\is_string($path) && \is_dir($path)) {
                $kinds[] = 'directory';
            } else {
                $kinds[] = 'gone';
            }
        }

        $accepted[$id] = ['paths' => $paths, 'kinds' => $kinds, 'at' => date(\DateTimeInterface::ATOM)];
        if (false === file_put_contents($file, json_encode($accepted, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES))) {
            return $this->json(['error' => 'cannot record the acceptance'], 500);
        }

        return $this->json(['id' => $id, 'accepted' => true, 'kinds' => $kinds, 'at' => $accepted[$id]['at']]);
    }

    private function storePath(): string
    {
        return $this->getParameter('kernel.cache_dir').'/open_files_accepted.json';
    }
}
