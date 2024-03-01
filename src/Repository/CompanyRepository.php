<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 *
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends ServiceEntityRepository
{
    private const IS_NOT_DELETED = 'company.isDeleted = false';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    /**
     * Get company by id
     *
     * @param Company $company
     * @return int
     */
    public function countCompanyClients($company): int
    {
        return $this->createQueryBuilder('client')
            ->select('COUNT(client.id)')
            ->andWhere('client.company = :company')
            ->andWhere(self::IS_NOT_DELETED)
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

    /**
    * @return Company[]
    */
    public function searchCompanyByNameOrEmailOrPhone($search): array
    {
        return $this->createQueryBuilder('company')
            ->andWhere('LOWER(company.name) LIKE LOWER(:searchValue) 
                OR LOWER(company.email) LIKE LOWER(:searchValue) 
                OR LOWER(company.phone) LIKE LOWER(:searchValue)')
            ->setParameter('searchValue', '%' . strtolower($search) . '%')
            ->orderBy('company.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
    * @return Company[]
    */
    public function filterCompaniesByStatus($status): array
    {
        $queryBuilder = $this->createQueryBuilder('company')
            ->orderBy('company.id', 'ASC');

        if ($status === 'true') {
            $queryBuilder->andWhere('company.isDeleted = :isDeleted')
                ->setParameter('isDeleted', false);
        } elseif ($status === 'false') {
            $queryBuilder->andWhere('company.isDeleted = :isDeleted')
                ->setParameter('isDeleted', true);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    //    /**
    //     * @return Company[] Returns an array of Company objects
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

    //    public function findOneBySomeField($value): ?Company
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
