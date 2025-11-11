<?php

namespace App\Controller\Api;

use App\Entity\DeviceLoginRequest;
use App\Repository\DeviceLoginRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/api/device-code', name: 'api_device_code', methods: ['POST'])]
final class DeviceCodeController extends AbstractController
{
    public function __construct(
        private readonly DeviceLoginRequestRepository $requests,
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $attempts = 0;
        do {
            $deviceCode = bin2hex(random_bytes(32));
            $userCode = $this->generateUserCode();
            $attempts++;
        } while ($this->requests->findOneByUserCode($userCode) && $attempts < 5);

        $request = (new DeviceLoginRequest())
            ->setDeviceCodeHash(hash('sha256', $deviceCode))
            ->setUserCode($userCode);

        $this->entityManager->persist($request);
        $this->entityManager->flush();

        $verificationUrl = $this->urlGenerator->generate('app_device_verify', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->json([
            'device_code' => $deviceCode,
            'user_code' => $userCode,
            'verification_uri' => $verificationUrl,
            'expires_in' => $request->getExpiresAt()->getTimestamp() - time(),
            'interval' => $request->getPollInterval(),
        ]);
    }

    private function generateUserCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $chars = '';
        for ($i = 0; $i < 8; ++$i) {
            $chars .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return substr(chunk_split($chars, 4, '-'), 0, 9);
    }
}
