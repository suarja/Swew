<?php

namespace App\Controller\Security;

use App\Repository\DeviceLoginRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/device', name: 'app_device_verify', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_USER')]
final class DeviceVerificationController extends AbstractController
{
    public function __construct(
        private readonly DeviceLoginRequestRepository $requests,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $message = null;
        $status = null;

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('device_verify', (string) $request->request->get('_csrf_token'))) {
                $message = 'Invalid CSRF token.';
                $status = 'error';
            } else {
            $rawCode = strtoupper(trim((string) $request->request->get('user_code')));
            $normalized = str_replace('-', '', $rawCode);

            if (strlen($normalized) !== 8) {
                $deviceRequest = null;
            } else {
                $formatted = substr(chunk_split($normalized, 4, '-'), 0, 9);
                $deviceRequest = $this->requests->findOneByUserCode($formatted);
            }

            if (!$deviceRequest) {
                $message = 'Code not found.';
                $status = 'error';
            } elseif ($deviceRequest->isExpired()) {
                $message = 'This code has expired. Generate a new one from the CLI.';
                $status = 'error';
            } elseif ($deviceRequest->isApproved()) {
                $message = 'This code was already approved.';
                $status = 'warning';
            } else {
                $deviceRequest->approve($this->getUser());
                $this->entityManager->flush();

                $message = 'Device approved. Return to your terminal.';
                $status = 'success';
            }
            }
        }

        return $this->render('security/device.html.twig', [
            'message' => $message,
            'status' => $status,
        ]);
    }
}
