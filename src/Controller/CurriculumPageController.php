<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CurriculumProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class CurriculumPageController extends AbstractController
{
    public function __construct(private readonly CurriculumProvider $curriculum)
    {
    }

    #[Route('/courses', name: 'app_courses', methods: ['GET'])]
    public function courses(): Response
    {
        return $this->render('courses/index.html.twig', [
            'courses' => $this->curriculum->catalog(),
        ]);
    }

    #[Route('/lessons/{slug}', name: 'app_lessons_show', requirements: ['slug' => '[A-Za-z0-9\-]+'], methods: ['GET'])]
    public function lesson(string $slug): Response
    {
        $lesson = $this->curriculum->lesson($slug);

        if ($lesson === null) {
            throw $this->createNotFoundException(sprintf('Lesson "%s" not found.', $slug));
        }

        return $this->render('lessons/show.html.twig', [
            'lesson' => $lesson,
        ]);
    }

    #[Route('/assignments/{code}', name: 'app_assignments_show', requirements: ['code' => '[A-Za-z0-9\-]+'], methods: ['GET'])]
    public function assignment(string $code): Response
    {
        $assignment = $this->curriculum->assignment($code);

        if ($assignment === null) {
            throw $this->createNotFoundException(sprintf('Assignment "%s" not found.', strtoupper($code)));
        }

        return $this->render('assignments/show.html.twig', [
            'assignment' => $assignment,
        ]);
    }
}
