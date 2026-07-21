<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TFSAppController extends AbstractController
{
    #[Route('/', name: 'tfs_app')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
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
}
