<?php

namespace App\Repository;

use App\Entity\ApiToken;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApiToken>
 */
class ApiTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiToken::class);
    }

    public function findValidToken(string $rawToken): ?ApiToken
    {
        $hash = hash('sha256', $rawToken);

        return $this->createQueryBuilder('token')
            ->andWhere('token.tokenHash = :hash')
            ->setParameter('hash', $hash)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(ApiToken $token): void
    {
        $em = $this->getEntityManager();
        $em->persist($token);
        $em->flush();
    }

    public function create(User $user, string $label, string $rawToken): ApiToken
    {
        return (new ApiToken())
            ->setUser($user)
            ->setLabel($label)
            ->setTokenHash(hash('sha256', $rawToken));
    }

    public function countActiveTokens(DateTimeImmutable $now): int
    {
        return (int) $this->createQueryBuilder('token')
            ->select('COUNT(token.id)')
            ->andWhere('token.expiresAt IS NULL OR token.expiresAt > :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
