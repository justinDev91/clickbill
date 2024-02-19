<?php

namespace App\Repository;

use App\Entity\Bill;
use App\Entity\Company;
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
     * Get all active bills
     *
     * @return Bill[]
     */
    public function findAllActiveBillsByCompany(Company $company): array
    {
        return $this->createQueryBuilder('bill')
            ->andWhere(self::IS_NOT_DELETED)
            ->andWhere('bill.company = :company')
            ->setParameter('company', $company)
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
    }

    /**
     * Get the total number of bills associated with a company based on status.
     *
     * @param Company $company The company for which to count bills.
     * @param string $status The status of the bills ('all', 'unpaid', 'paid').
     * @return int The total number of bills based on status.
     * @throws \Doctrine\ORM\NoResultException|\Doctrine\ORM\NonUniqueResultException
     */
    public function getTotalBillCountByStatusForCompany(Company $company, string $status = 'all'): int
    {
        try {
            $queryBuilder = $this->createQueryBuilder('bill')
                ->select('COUNT(bill.id)')
                ->andWhere('bill.company = :company')
                ->andWhere(self::IS_NOT_DELETED)
                ->setParameter('company', $company);

            switch ($status) {
                case 'unpaid':
                    $queryBuilder->andWhere("bill.status != 'Acquitté'");
                    break;
                case 'paid':
                    $queryBuilder->andWhere("bill.status = 'Acquitté'");
                    break;
                default:
                    break;
            }

            return $queryBuilder->getQuery()->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException | \Doctrine\ORM\NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * Filters bills by status
     *
     * @param string $status The status to filter by.
     * @return array|null An array of bill matching the specified status.
     */
    public function filterBillsByStatus($status, $company): ?array
    {
        return $this->createQueryBuilder('bill')
            ->andWhere('bill.status = :status')
            ->andWhere('bill.company = :company')
            ->andWhere(self::IS_NOT_DELETED)
            ->setParameter('status', $status)
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

    /**
     * search bills by client name or email
     *
     * @return Quote[]
     */
    public function searchBillsByClientNameOrEmail($term, $company): ?array
    {
        return $this->createQueryBuilder('bill')
            ->join('bill.client', 'client')
            ->andWhere('
                client.firstName LIKE :searchTerm OR
                client.lastName LIKE :searchTerm OR
                client.email LIKE :searchTerm')
            ->andWhere('bill.company = :company')
            ->andWhere(self::IS_NOT_DELETED)
            ->setParameter('searchTerm', '%' . $term . '%')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
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
