<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
#[IsGranted('ROLE_USER')]
final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();

        return $this->json([
            'email' => $user?->getUserIdentifier(),
            'name' => $user?->getName(),
            'roles' => $user?->getRoles(),
        ]);
    }
}
