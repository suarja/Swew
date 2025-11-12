<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Assignment;
use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait CurriculumFixtureTrait
{
    private function createUserAndLogin(KernelBrowser $client): User
    {
        $user = (new User())
            ->setEmail(sprintf('learner+%s@example.com', uniqid()))
            ->setName('Fixture User')
            ->setRoles(['ROLE_USER'])
            ->setPassword('test');

        $em = $this->entityManager();
        $em->persist($user);
        $em->flush();

        $client->loginUser($user);

        return $user;
    }

    /**
     * @return array{course: Course, lesson: Lesson, assignment: Assignment}
     */
    private function persistCurriculumFixture(string $status = 'live'): array
    {
        $course = (new Course())
            ->setTitle(sprintf('Course %s', strtoupper($status)))
            ->setSlug(sprintf('course-%s', uniqid()))
            ->setSummary('Fixture course summary')
            ->setStatus($status);

        $lesson = (new Lesson())
            ->setTitle('Lesson '.uniqid())
            ->setSlug(sprintf('lesson-%s', uniqid()))
            ->setSummary('Fixture lesson summary')
            ->setContent('Fixture lesson content')
            ->setSequencePosition(1);

        $assignment = (new Assignment())
            ->setTitle('Assignment '.uniqid())
            ->setCode(sprintf('boot-%s', substr(uniqid(), -4)))
            ->setDescription('Fixture assignment description')
            ->setCliSteps("$ swew doctor")
            ->setEvaluationNotes('Be honest in reflections.')
            ->setDisplayOrder(1);

        $course->addLesson($lesson);
        $lesson->addAssignment($assignment);

        $em = $this->entityManager();
        $em->persist($course);
        $em->persist($lesson);
        $em->persist($assignment);
        $em->flush();

        return [
            'course' => $course,
            'lesson' => $lesson,
            'assignment' => $assignment,
        ];
    }

    private function entityManager(): EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);

        return $em;
    }
}
