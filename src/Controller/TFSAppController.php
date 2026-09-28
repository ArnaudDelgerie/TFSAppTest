<?php

namespace App\Controller;

use App\Message\PingMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

final class TFSAppController extends AbstractController
{
    #[Route('/', name: 'tfs_app')]
    public function index(Request $request, Authorization $authorization): Response
    {
        // The hub no longer accepts anonymous subscriptions (station plan
        // 033): mint a same-origin mercureAuthorization cookie scoped to
        // the demo topic on every page load, or the EventSource below gets
        // a 401 on subscribe.
        $authorization->setCookie($request, [PingMessage::TOPIC]);

        $response = $this->render('index.html.twig', [
            // Lets a tester see which mode the running instance is in
            // without guessing from dispatch timing alone (plan 028,
            // CONTRACT.md §3's TFS_ASYNC_WORKER).
            'async_worker_mode' => ($_SERVER['TFS_ASYNC_WORKER'] ?? getenv('TFS_ASYNC_WORKER')) === '1',
            'mercure_public_url' => $_SERVER['MERCURE_PUBLIC_URL'] ?? getenv('MERCURE_PUBLIC_URL') ?: null,
            'ping_topic' => PingMessage::TOPIC,
            // Reported, not assumed (hub plan 050, CONTRACT.md §3): whether
            // this launch actually granted the microphone, never whether the
            // manifest merely asked for it.
            'media_microphone' => ($_SERVER['TFS_MEDIA_MICROPHONE'] ?? getenv('TFS_MEDIA_MICROPHONE')) === '1',
        ]);

        // The hub's own default CSP has no media-src, so it falls back to
        // default-src 'self' — which does not cover blob:. The recorded
        // clip's playback below is a blob: URL (CONTRACT.md §4's "a
        // response that already carries one of those field names keeps its
        // own" override rule), so this page needs its own policy, otherwise
        // identical to the host's default plus that one directive.
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; connect-src 'self' ipc: http://ipc.localhost; "
            . "img-src 'self' data:; style-src 'self' 'unsafe-inline'; "
            . "script-src 'self' 'unsafe-inline'; media-src 'self' blob:; "
            . "object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'"
        );

        return $response;
    }

    // #[Route('/healthz', name: 'healthz', methods: ['GET'])]
    public function healthz(): JsonResponse
    {
        return $this->json(['status' => 'ok']);
    }

    #[Route('/account', name: 'account')]
    public function account(): Response
    {
        return $this->render('account.html.twig');
    }

    /**
     * Demo dispatch route for the async-worker/Mercure validation (plan
     * 028): dispatches PingMessage and returns its correlation id
     * immediately. Completion is pushed back live via Mercure by
     * PingMessageHandler, not returned here — the point of the demo is to
     * show the round trip is asynchronous.
     */
    #[Route('/ping', name: 'ping', methods: ['POST'])]
    #[IsCsrfTokenValid('ping', tokenKey: '_csrf_token')]
    public function ping(MessageBusInterface $bus): JsonResponse
    {
        $correlationId = bin2hex(random_bytes(8));
        $bus->dispatch(new PingMessage($correlationId, 'Hello from TFSAppTest'));

        return $this->json(['correlationId' => $correlationId]);
    }

    /**
     * Fixture for the station's plan 059 validation: reports back the
     * filename and byte size Symfony actually received, so a manual tester
     * dragging a file from the file manager (or picking one through the
     * WebKit file chooser) can see whether the upload really went through.
     */
    #[Route('/upload', name: 'upload', methods: ['POST'])]
    #[IsCsrfTokenValid('upload', tokenKey: '_csrf_token')]
    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file) {
            return $this->json(['error' => 'no file received'], 400);
        }

        return $this->json([
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);
    }

    /**
     * Fixture for the station's plan 034 step 3: the station's default
     * Content-Security-Policy is set with Caddy's `?` form (build/Caddyfile.desktop),
     * which only sets a header the response doesn't already carry. This route
     * emits its own, deliberately different policy to confirm the station's
     * default steps aside instead of appearing alongside it.
     */
    #[Route('/csp-override-demo', name: 'csp_override_demo')]
    public function cspOverrideDemo(): Response
    {
        $response = new Response('CSP override demo');
        $response->headers->set('Content-Security-Policy', "default-src 'none'");

        return $response;
    }
}
