<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Fixture for the `actions.secrets` bridge (CONTRACT.md §7): round-trips
 * the secret store through the
 * loopback HTTP bridge, exercising the exact transport the TFSAppBundle
 * client uses (the bundle's bridge wire contract).
 * The WebView side of the same demo (Tauri's own invoke()) lives entirely
 * in index.html.twig/JS — this controller never touches that path.
 */
final class SecretsController extends AbstractController
{
    #[Route('/secrets/bridge/{action}', name: 'secrets_bridge', methods: ['POST'], requirements: ['action' => 'has|get|set|delete'])]
    #[IsCsrfTokenValid('secrets_bridge', tokenKey: '_csrf_token')]
    public function bridge(string $action, Request $request, HttpClientInterface $client): JsonResponse
    {
        $bridgeUrl = $_SERVER['TFS_BRIDGE_URL'] ?? getenv('TFS_BRIDGE_URL') ?: null;
        $bridgeToken = $_SERVER['TFS_BRIDGE_TOKEN'] ?? getenv('TFS_BRIDGE_TOKEN') ?: null;
        if (!$bridgeUrl || !$bridgeToken) {
            return $this->json(['available' => false]);
        }

        $payload = ['key' => (string) $request->request->get('key', '')];
        if ('set' === $action) {
            $payload['value'] = (string) $request->request->get('value', '');
        }

        $response = $client->request('POST', rtrim($bridgeUrl, '/').'/secrets/'.$action, [
            'auth_bearer' => $bridgeToken,
            'json' => $payload,
        ]);

        return $this->json(['available' => true] + $response->toArray(false), $response->getStatusCode());
    }
}
