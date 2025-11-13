<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Service\ProgressService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/progress', name: 'api_progress', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final class ProgressController extends AbstractController
{
    public function __construct(private readonly ProgressService $progress)
    {
    }

    public function __invoke(): JsonResponse
    {
        $user = $this->requireUser();

        return $this->json($this->progress->buildSnapshot($user));
    }

    private function requireUser(): User
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('Authenticated user expected.');
        }

        return $user;
    }
}
