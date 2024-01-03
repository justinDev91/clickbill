<?php

namespace App\Repository;

use App\Entity\ClientInteraction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClientInteraction>
 *
 * @method ClientInteraction|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientInteraction|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientInteraction[]    findAll()
 * @method ClientInteraction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientInteractionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientInteraction::class);
    }

//    /**
//     * @return ClientInteraction[] Returns an array of ClientInteraction objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ClientInteraction
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
