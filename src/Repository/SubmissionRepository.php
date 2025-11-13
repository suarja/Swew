<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Assignment;
use App\Entity\Submission;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Submission>
 */
final class SubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Submission::class);
    }

    /**
     * @param int[] $assignmentIds
     * @return array<int, Submission>
     */
    public function findLatestByAssignments(User $user, array $assignmentIds): array
    {
        if ($assignmentIds === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('submission')
            ->andWhere('submission.user = :user')
            ->andWhere('submission.assignment IN (:assignments)')
            ->setParameter('user', $user)
            ->setParameter('assignments', $assignmentIds)
            ->orderBy('submission.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $latest = [];
        /** @var Submission $submission */
        foreach ($rows as $submission) {
            $assignmentId = $submission->getAssignment()?->getId();
            if ($assignmentId === null || isset($latest[$assignmentId])) {
                continue;
            }

            $latest[$assignmentId] = $submission;
        }

        return $latest;
    }

    public function create(User $user, Assignment $assignment): Submission
    {
        return (new Submission())
            ->setUser($user)
            ->setAssignment($assignment);
    }
}
