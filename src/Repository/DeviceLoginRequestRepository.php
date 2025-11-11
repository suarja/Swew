<?php

namespace App\Repository;

use App\Entity\DeviceLoginRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DeviceLoginRequest>
 */
class DeviceLoginRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeviceLoginRequest::class);
    }

    public function findOneByDeviceCode(string $deviceCode): ?DeviceLoginRequest
    {
        return $this->createQueryBuilder('request')
            ->andWhere('request.deviceCodeHash = :hash')
            ->setParameter('hash', hash('sha256', $deviceCode))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByUserCode(string $userCode): ?DeviceLoginRequest
    {
        return $this->createQueryBuilder('request')
            ->andWhere('request.userCode = :code')
            ->setParameter('code', $userCode)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(DeviceLoginRequest $request, bool $flush = true): void
    {
        $this->_em->persist($request);

        if ($flush) {
            $this->_em->flush();
        }
    }
}
