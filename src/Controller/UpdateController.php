<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Fixture for station plan 035: round-trips actions.update through the
 * loopback HTTP bridge, exercising the exact transport a future
 * TFSAppBundle client would use (see CONTRACT.md's bridge wire contract).
 * The WebView side of the same demo (Tauri's own invoke()) lives entirely
 * in index.html.twig/JS — this controller never touches that path. No CSRF
 * token here, unlike SecretsController::bridge — this route only ever
 * reads, it never mutates anything.
 */
final class UpdateController extends AbstractController
{
    #[Route('/update/bridge', name: 'update_bridge', methods: ['GET'])]
    public function bridge(HttpClientInterface $client): JsonResponse
    {
        $bridgeUrl = $_SERVER['TFS_BRIDGE_URL'] ?? getenv('TFS_BRIDGE_URL') ?: null;
        $bridgeToken = $_SERVER['TFS_BRIDGE_TOKEN'] ?? getenv('TFS_BRIDGE_TOKEN') ?: null;
        if (!$bridgeUrl || !$bridgeToken) {
            return $this->json(['available' => false]);
        }

        $response = $client->request('GET', rtrim($bridgeUrl, '/').'/update/check', [
            'auth_bearer' => $bridgeToken,
        ]);

        return $this->json(['available' => true] + $response->toArray(false), $response->getStatusCode());
    }
}
