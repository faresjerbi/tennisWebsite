<?php

namespace App\Repository;

use App\Entity\LocationTerrain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LocationTerrain>
 *
 * @method LocationTerrain|null find($id, $lockMode = null, $lockVersion = null)
 * @method LocationTerrain|null findOneBy(array $criteria, array $orderBy = null)
 * @method LocationTerrain[]    findAll()
 * @method LocationTerrain[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocationTerrainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocationTerrain::class);
    }

//    /**
//     * @return LocationTerrain[] Returns an array of LocationTerrain objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?LocationTerrain
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
