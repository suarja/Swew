<?php

namespace App\Controller\Api;

use App\Entity\ApiToken;
use App\Repository\DeviceLoginRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/device-token', name: 'api_device_token', methods: ['POST'])]
final class DeviceTokenController extends AbstractController
{
    public function __construct(
        private readonly DeviceLoginRequestRepository $requests,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent() ?: '[]', true);
        $deviceCode = $payload['device_code'] ?? null;

        if (!$deviceCode) {
            return $this->json(['error' => 'invalid_request'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $deviceRequest = $this->requests->findOneByDeviceCode($deviceCode);
        if (!$deviceRequest || $deviceRequest->isExpired()) {
            return $this->json(['error' => 'expired_token'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if ($deviceRequest->isConsumed()) {
            return $this->json(['error' => 'access_denied'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (!$deviceRequest->isApproved() || null === $deviceRequest->getUser()) {
            return $this->json([
                'error' => 'authorization_pending',
                'interval' => $deviceRequest->getPollInterval(),
            ], JsonResponse::HTTP_ACCEPTED);
        }

        $rawToken = bin2hex(random_bytes(32));
        $apiToken = (new ApiToken())
            ->setLabel('CLI Device Flow')
            ->setTokenHash(hash('sha256', $rawToken))
            ->setUser($deviceRequest->getUser());

        $this->entityManager->persist($apiToken);
        $deviceRequest->consume();
        $this->entityManager->flush();

        return $this->json([
            'access_token' => $rawToken,
            'token_type' => 'bearer',
            'expires_in' => 0,
        ]);
    }
}
