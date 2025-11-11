<?php

namespace App\Repository;

use App\Entity\ApiToken;
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
        $this->_em->persist($token);
        $this->_em->flush();
    }
}
