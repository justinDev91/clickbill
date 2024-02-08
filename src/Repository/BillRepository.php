<?php

namespace App\Repository;

use App\Entity\Bill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends ServiceEntityRepository<Bill>
 *
 * @method Bill|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bill|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bill[]    findAll()
 * @method Bill[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BillRepository extends ServiceEntityRepository
{
    private const IS_NOT_DELETED = 'bill.isDeleted = false';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bill::class);
    }

    
    /**
     * Get all active clients
     *
     * @return Client[]
     */
    public function findAllActiveBills(): array
    {
        return $this->createQueryBuilder('bill')
            ->andWhere(self::IS_NOT_DELETED)
            ->getQuery()
            ->getResult();
    }

    public function findByQuoteId($id): ArrayCollection
    {
       $results = $this->createQueryBuilder('bill')
           ->andWhere('bill.quote = :quote AND bill.isDeleted = false')
           ->setParameter('quote', $id)
           ->getQuery()
           ->getResult();
        return new ArrayCollection($results);
       ;
    }


//    /**
//     * @return Bill[] Returns an array of Bill objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Bill
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
