<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HealthController extends AbstractController
{
    #[Route('/healthz', name: 'app_health', methods: ['GET'])]
    public function index(): Response
    {
        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
