<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Assignment;
use App\Entity\Course;
use App\Entity\Lesson;
use App\Repository\AssignmentRepository;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use DateTimeInterface;

/**
 * Centralized read model for curriculum data so APIs, Twig views, and CLI hooks stay in sync.
 */
final class CurriculumProvider
{
    /**
     * We only surface courses that are visible to learners. Drafts and archived courses stay hidden.
     *
     * @var string[]
     */
    private const VISIBLE_STATUSES = ['live', 'preview'];

    public function __construct(
        private readonly CourseRepository $courses,
        private readonly LessonRepository $lessons,
        private readonly AssignmentRepository $assignments,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function catalog(): array
    {
        $qb = $this->courses->createQueryBuilder('course')
            ->leftJoin('course.lessons', 'lesson')
            ->addSelect('lesson')
            ->where('course.status IN (:statuses)')
            ->setParameter('statuses', self::VISIBLE_STATUSES)
            ->orderBy('course.updatedAt', 'DESC')
            ->addOrderBy('lesson.sequencePosition', 'ASC');

        /** @var Course[] $courses */
        $courses = $qb->getQuery()->getResult();

        return array_map(fn (Course $course): array => $this->normalizeCourse($course), $courses);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function lesson(string $slug): ?array
    {
        $qb = $this->lessons->createQueryBuilder('lesson')
            ->leftJoin('lesson.course', 'course')
            ->addSelect('course')
            ->leftJoin('lesson.assignments', 'assignment')
            ->addSelect('assignment')
            ->where('lesson.slug = :slug')
            ->andWhere('course.status IN (:statuses)')
            ->setParameter('slug', $slug)
            ->setParameter('statuses', self::VISIBLE_STATUSES)
            ->orderBy('assignment.displayOrder', 'ASC');

        /** @var Lesson|null $lesson */
        $lesson = $qb->getQuery()->getOneOrNullResult();

        if ($lesson === null) {
            return null;
        }

        return $this->normalizeLesson($lesson);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function assignment(string $code): ?array
    {
        $normalizedCode = strtoupper($code);

        $qb = $this->assignments->createQueryBuilder('assignment')
            ->leftJoin('assignment.lesson', 'lesson')
            ->addSelect('lesson')
            ->leftJoin('lesson.course', 'course')
            ->addSelect('course')
            ->where('assignment.code = :code')
            ->andWhere('course.status IN (:statuses)')
            ->setParameter('code', $normalizedCode)
            ->setParameter('statuses', self::VISIBLE_STATUSES);

        /** @var Assignment|null $assignment */
        $assignment = $qb->getQuery()->getOneOrNullResult();

        if ($assignment === null) {
            return null;
        }

        return $this->normalizeAssignment($assignment, includeParents: true);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeCourse(Course $course): array
    {
        $lessons = [];
        foreach ($course->getLessons() as $lesson) {
            $lessons[] = $this->lessonExcerpt($lesson);
        }

        return [
            'slug' => $course->getSlug(),
            'title' => $course->getTitle(),
            'summary' => $course->getSummary(),
            'status' => $course->getStatus(),
            'lessonCount' => count($lessons),
            'lessons' => $lessons,
            'updatedAt' => $course->getUpdatedAt()->format(DateTimeInterface::ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeLesson(Lesson $lesson): array
    {
        $assignments = [];
        foreach ($lesson->getAssignments() as $assignment) {
            $assignments[] = $this->assignmentExcerpt($assignment);
        }

        return [
            'slug' => $lesson->getSlug(),
            'title' => $lesson->getTitle(),
            'summary' => $lesson->getSummary(),
            'content' => $lesson->getContent(),
            'sequence' => $lesson->getSequencePosition(),
            'course' => $this->courseSummary($lesson->getCourse()),
            'assignments' => $assignments,
            'updatedAt' => $lesson->getUpdatedAt()->format(DateTimeInterface::ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function assignmentExcerpt(Assignment $assignment): array
    {
        return [
            'code' => $assignment->getCode(),
            'title' => $assignment->getTitle(),
            'description' => $assignment->getDescription(),
            'cliSteps' => $assignment->getCliSteps(),
            'evaluationNotes' => $assignment->getEvaluationNotes(),
            'displayOrder' => $assignment->getDisplayOrder(),
            'updatedAt' => $assignment->getUpdatedAt()->format(DateTimeInterface::ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function lessonExcerpt(Lesson $lesson): array
    {
        return [
            'slug' => $lesson->getSlug(),
            'title' => $lesson->getTitle(),
            'summary' => $lesson->getSummary(),
            'sequence' => $lesson->getSequencePosition(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeAssignment(Assignment $assignment, bool $includeParents = false): array
    {
        $payload = $this->assignmentExcerpt($assignment);

        if ($includeParents) {
            $lesson = $assignment->getLesson();
            $payload['lesson'] = $lesson ? $this->lessonExcerpt($lesson) : null;
            $payload['course'] = $this->courseSummary($lesson?->getCourse());
        }

        return $payload;
    }

    /**
     * @return array<string, string>|null
     */
    private function courseSummary(?Course $course): ?array
    {
        if ($course === null) {
            return null;
        }

        return [
            'slug' => $course->getSlug(),
            'title' => $course->getTitle(),
            'status' => $course->getStatus(),
        ];
    }
}
