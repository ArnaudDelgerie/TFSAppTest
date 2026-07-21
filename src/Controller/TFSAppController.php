<?php

namespace App\Controller;

use App\Message\PingMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

final class TFSAppController extends AbstractController
{
    #[Route('/', name: 'tfs_app')]
    public function index(): Response
    {
        return $this->render('index.html.twig', [
            // Lets a tester see which mode the running instance is in
            // without guessing from dispatch timing alone (plan 028,
            // CONTRACT.md §3's TFS_ASYNC_WORKER).
            'async_worker_mode' => ($_SERVER['TFS_ASYNC_WORKER'] ?? getenv('TFS_ASYNC_WORKER')) === '1',
            'mercure_public_url' => $_SERVER['MERCURE_PUBLIC_URL'] ?? getenv('MERCURE_PUBLIC_URL') ?: null,
            'ping_topic' => PingMessage::TOPIC,
        ]);
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
}
