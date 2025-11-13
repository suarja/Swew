<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Assignment;
use App\Entity\Submission;
use App\Entity\User;
use App\Repository\AssignmentRepository;
use App\Repository\SubmissionRepository;
use App\Service\ProgressBroadcaster;
use App\Service\ProgressService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/submissions', name: 'api_submissions', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class SubmissionController extends AbstractController
{
    public function __construct(
        private readonly AssignmentRepository $assignments,
        private readonly SubmissionRepository $submissions,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProgressService $progress,
        private readonly ProgressBroadcaster $broadcaster,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->requireUser();
        $payload = $this->decodePayload($request);

        $assignment = $this->findAssignment($payload['assignment'] ?? null);
        $submission = $this->storeSubmission($user, $assignment, $payload);

        $snapshot = $this->progress->buildSnapshot($user);

        $assignmentPayload = [
            'assignment' => $assignment->getCode(),
            'status' => $submission->getStatus(),
            'submittedAt' => $submission->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'nextAssignment' => $snapshot['nextAssignment'],
        ];

        $this->broadcaster->broadcastAssignment($user, $assignmentPayload);
        $this->broadcaster->broadcastProgress($user, $snapshot);

        return $this->json($assignmentPayload, JsonResponse::HTTP_ACCEPTED);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodePayload(Request $request): array
    {
        $content = $request->getContent();
        if ($content === '' || $content === false) {
            throw new BadRequestHttpException('Empty payload.');
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new BadRequestHttpException('Invalid JSON payload.');
        }

        if (!is_array($data)) {
            throw new BadRequestHttpException('Invalid submission payload.');
        }

        return $data;
    }

    private function findAssignment(?string $code): Assignment
    {
        if ($code === null) {
            throw new BadRequestHttpException('Assignment code missing.');
        }

        $assignment = $this->assignments->findOneBy(['code' => strtoupper($code)]);
        if (!$assignment) {
            throw $this->createNotFoundException(sprintf('Assignment "%s" not found.', strtoupper($code)));
        }

        return $assignment;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function storeSubmission(User $user, Assignment $assignment, array $payload): Submission
    {
        $statusInput = strtolower((string) ($payload['status'] ?? ''));
        $status = match ($statusInput) {
            'pass', 'passed' => Submission::STATUS_PASSED,
            'fail', 'failed' => Submission::STATUS_FAILED,
            default => throw new BadRequestHttpException('Submission status must be "pass" or "fail".'),
        };

        $cliVersion = (string) ($payload['cliVersion'] ?? '');
        $kitVersion = (int) ($payload['kitVersion'] ?? 0);

        if ($cliVersion === '') {
            throw new BadRequestHttpException('Missing CLI version.');
        }

        if ($kitVersion <= 0) {
            throw new BadRequestHttpException('Invalid kit version.');
        }

        $submission = $this->submissions->create($user, $assignment)
            ->setStatus($status)
            ->setCliVersion($cliVersion)
            ->setKitVersion($kitVersion)
            ->setChecks($this->ensureArray($payload['checks'] ?? null))
            ->setPrompts($this->ensureArray($payload['prompts'] ?? null))
            ->setSystemInfo($this->ensureArray($payload['system'] ?? null))
            ->setLogs(isset($payload['logs']) ? (string) $payload['logs'] : null);

        $this->entityManager->persist($submission);
        $this->entityManager->flush();

        return $submission;
    }

    /**
     * @param mixed $value
     * @return array<string, mixed>|null
     */
    private function ensureArray(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw new BadRequestHttpException('Checks, prompts, and system info must be arrays.');
        }

        return $value;
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
