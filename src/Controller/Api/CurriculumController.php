<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\CurriculumProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
#[IsGranted('ROLE_USER')]
final class CurriculumController extends AbstractController
{
    public function __construct(private readonly CurriculumProvider $curriculum)
    {
    }

    #[Route('/courses', name: 'courses', methods: ['GET'])]
    public function courses(): JsonResponse
    {
        return $this->json(['courses' => $this->curriculum->catalog()]);
    }

    #[Route('/lessons/{slug}', name: 'lessons_show', methods: ['GET'])]
    public function lesson(string $slug): JsonResponse
    {
        $lesson = $this->curriculum->lesson($slug);

        if ($lesson === null) {
            throw $this->createNotFoundException(sprintf('Lesson "%s" not found.', $slug));
        }

        return $this->json(['lesson' => $lesson]);
    }

    #[Route('/assignments/{code}', name: 'assignments_show', requirements: ['code' => '[A-Za-z0-9\-]+'], methods: ['GET'])]
    public function assignment(string $code): JsonResponse
    {
        $assignment = $this->curriculum->assignment($code);

        if ($assignment === null) {
            throw $this->createNotFoundException(sprintf('Assignment "%s" not found.', strtoupper($code)));
        }

        return $this->json(['assignment' => $assignment]);
    }
}
