<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Assignment;
use App\Entity\Submission;
use App\Entity\User;
use App\Repository\AssignmentRepository;
use App\Repository\SubmissionRepository;
use DateTimeInterface;

final class ProgressService
{
    public function __construct(
        private readonly AssignmentRepository $assignments,
        private readonly SubmissionRepository $submissions,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSnapshot(User $user): array
    {
        $assignmentEntities = $this->fetchVisibleAssignments();

        if ($assignmentEntities === []) {
            return [
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName(),
                ],
                'course' => null,
                'lesson' => null,
                'nextAssignment' => null,
                'assignments' => [],
            ];
        }

        $activeCourseId = $assignmentEntities[0]->getLesson()?->getCourse()?->getId();
        $assignmentEntities = array_values(array_filter(
            $assignmentEntities,
            static fn (Assignment $assignment): bool => $assignment->getLesson()?->getCourse()?->getId() === $activeCourseId
        ));
        $assignmentIds = array_map(
            static fn (Assignment $assignment): int => $assignment->getId(),
            $assignmentEntities,
        );
        $latestSubmissions = $this->submissions->findLatestByAssignments($user, $assignmentIds);

        $assignments = [];
        $nextAssignment = null;
        $nextLesson = null;
        $currentCourse = null;

        foreach ($assignmentEntities as $assignment) {
            $submission = null;
            $assignmentId = $assignment->getId();
            if ($assignmentId !== null && isset($latestSubmissions[$assignmentId])) {
                $submission = $latestSubmissions[$assignmentId];
            }

            $status = $submission?->getStatus() ?? Submission::STATUS_PENDING;

            if ($nextAssignment === null && $status === Submission::STATUS_PENDING) {
                $nextAssignment = $assignment;
                $nextLesson = $assignment->getLesson();
                $currentCourse = $assignment->getLesson()?->getCourse();
            }

            $assignments[] = $this->assignmentPayload($assignment, $status, $submission);
        }

        $currentCourse = $currentCourse ?? $assignmentEntities[0]->getLesson()?->getCourse();

        return [
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
            ],
            'course' => $currentCourse ? [
                'slug' => $currentCourse->getSlug(),
                'title' => $currentCourse->getTitle(),
            ] : null,
            'lesson' => $nextLesson ? [
                'slug' => $nextLesson->getSlug(),
                'title' => $nextLesson->getTitle(),
                'sequence' => $nextLesson->getSequencePosition(),
            ] : null,
            'nextAssignment' => $nextAssignment ? [
                'code' => $nextAssignment->getCode(),
                'title' => $nextAssignment->getTitle(),
            ] : null,
            'assignments' => $assignments,
        ];
    }

    /**
     * @return Assignment[]
     */
    private function fetchVisibleAssignments(): array
    {
        return $this->assignments->createQueryBuilder('assignment')
            ->leftJoin('assignment.lesson', 'lesson')
            ->addSelect('lesson')
            ->leftJoin('lesson.course', 'course')
            ->addSelect('course')
            ->andWhere('course.status IN (:statuses)')
            ->setParameter('statuses', CurriculumProvider::VISIBLE_STATUSES)
            ->orderBy('course.id', 'DESC')
            ->addOrderBy('lesson.sequencePosition', 'ASC')
            ->addOrderBy('assignment.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, mixed>
     */
    private function assignmentPayload(Assignment $assignment, string $status, ?Submission $submission): array
    {
        return [
            'code' => $assignment->getCode(),
            'title' => $assignment->getTitle(),
            'lesson' => [
                'slug' => $assignment->getLesson()?->getSlug(),
                'title' => $assignment->getLesson()?->getTitle(),
            ],
            'status' => $status,
            'submittedAt' => $submission ? $submission->getCreatedAt()->format(DateTimeInterface::ATOM) : null,
        ];
    }
}
