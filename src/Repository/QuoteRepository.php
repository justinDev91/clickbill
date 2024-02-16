<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Quote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quote>
 *
 * @method Quote|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quote|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quote[]    findAll()
 * @method Quote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuoteRepository extends ServiceEntityRepository
{
    private const IS_NOT_DELETED = 'quote.isDeleted = false';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quote::class);
    }

    /**
     * Get all active quotes
     *
     * @return Quote[]
     */
    public function findAllActiveQuotes($company): array
    {
        return $this->createQueryBuilder('quote')
            ->andWhere(self::IS_NOT_DELETED)
            ->andWhere('quote.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

    /**
     * search quote by client name or email
     *
     * @return Quote[]
     */
    public function searchQuoteByClientNameOrEmail($term, $company): ?array
    {
        return $this->createQueryBuilder('quote')
            ->join('quote.client', 'client')
            ->andWhere('
                client.firstName LIKE :searchTerm OR
                client.lastName LIKE :searchTerm OR
                client.email LIKE :searchTerm')
            ->andWhere('quote.company = :company')
            ->andWhere(self::IS_NOT_DELETED)
            ->setParameter('searchTerm', '%' . $term . '%')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get the total number of quotes associated with a company.
     *
     * @param Company $company The company for which to count quotes.
     * @return int The total number of quotes associated with the company.
     * @throws \Doctrine\ORM\NoResultException|\Doctrine\ORM\NonUniqueResultException
     */
    public function getTotalQuoteCountForCompany(Company $company): int
    {
        try {
            return $this->createQueryBuilder('quote')
                ->select('COUNT(quote.id)')
                ->andWhere('quote.company = :company')
                ->andWhere(self::IS_NOT_DELETED)
                ->setParameter('company', $company)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException | \Doctrine\ORM\NonUniqueResultException $e) {
            return 0;
        }
    }

    //    /**
    //     * @return Quote[] Returns an array of Quote objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('q.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Quote
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
