<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Fixture for the hub's plan 055 end-to-end validation: two independent
 * simulated background jobs whose guards go through the loopback bridge,
 * exactly the way a real worker's register-before-work / remove-in-finally
 * pair would (see the hub contract's close_guard section). The jobs are
 * fakes on purpose — the guard lifetime is the thing under test, not the
 * work. Hand-written and deliberately crude, like DurableUploadController
 * and SavePathController.
 *
 * Two jobs, two stable IDs, one route pair: `export` and `mail` are distinct
 * kinds with distinct IDs, so starting one never touches the other's
 * protection, and "complete" removes only the named job's guard — the
 * two-simultaneous-jobs rule, made clickable. "fail" registers the guard
 * and then reports the job failed *without* removing it: the warning has
 * to outlive the task that crashed, until an explicit completion or the
 * next launch clears it.
 */
final class CloseGuardController extends AbstractController
{
    private const JOBS = ['export' => 'export:probe', 'mail' => 'mail:probe'];

    #[Route('/close-guard/job/{job}/{action}', name: 'close_guard_job', methods: ['POST'], requirements: ['job' => 'export|mail', 'action' => 'start|complete|fail'])]
    #[IsCsrfTokenValid('close_guard_job', tokenKey: '_csrf_token')]
    public function job(string $job, string $action, Request $request, HttpClientInterface $client): JsonResponse
    {
        $id = self::JOBS[$job];

        $bridgeUrl = $_SERVER['TFS_BRIDGE_URL'] ?? getenv('TFS_BRIDGE_URL') ?: null;
        $bridgeToken = $_SERVER['TFS_BRIDGE_TOKEN'] ?? getenv('TFS_BRIDGE_TOKEN') ?: null;
        if (!$bridgeUrl || !$bridgeToken) {
            return $this->json(['available' => false]);
        }

        // Register is idempotent per ID, so a restart of the same job kind is
        // harmless; "start" registers before any work would begin.
        if ('start' === $action || 'fail' === $action) {
            $registered = $client->request('POST', rtrim($bridgeUrl, '/').'/close-guard/register', [
                'auth_bearer' => $bridgeToken,
                'json' => ['id' => $id],
            ]);
            if (200 !== $registered->getStatusCode()) {
                return $this->json(
                    ['available' => true] + $registered->toArray(false),
                    $registered->getStatusCode(),
                );
            }
        }

        if ('start' === $action) {
            return $this->json(['available' => true, 'ok' => true, 'job' => $job, 'id' => $id, 'state' => 'running']);
        }

        if ('fail' === $action) {
            // The task failed after registering: no finally ran, the guard
            // stays until an explicit completion or the process exits.
            return $this->json(['available' => true, 'ok' => false, 'job' => $job, 'id' => $id, 'state' => 'failed', 'error' => 'simulated failure — guard left standing'], 500);
        }

        // "complete" is the finally: removal of an absent guard also succeeds,
        // so completing a job that never started is a plain ok.
        $removed = $client->request('POST', rtrim($bridgeUrl, '/').'/close-guard/remove', [
            'auth_bearer' => $bridgeToken,
            'json' => ['id' => $id],
        ]);

        return $this->json(
            ['available' => true] + $removed->toArray(false) + ['job' => $job, 'id' => $id, 'state' => 'done'],
            $removed->getStatusCode(),
        );
    }
}
