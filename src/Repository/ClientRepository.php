<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 *
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository
{
    private const IS_NOT_DELETED = 'client.isDeleted = false';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * Search clients by firstName/lastName or email
     *
     * @param string $value
     * @return Client
     */
    public function searchClientByNameOrEmail($term, $company): ?array
    {
        return $this->createQueryBuilder('client')
            ->andWhere('
                client.firstName LIKE :searchTerm OR
                client.lastName LIKE :searchTerm OR
                client.email LIKE :searchTerm')
            ->andWhere(self::IS_NOT_DELETED)
            ->andWhere('client.company = :company')
            ->setParameter('searchTerm', '%' . $term . '%')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

    /**
     * Filters clients by bill status
     *
     * @param string $status The status to filter by.
     * @return array|null An array of clients matching the specified status.
     */
    public function filterClientsByStatus($status, $company): ?array
    {
        return $this->createQueryBuilder('client')
            ->join('client.quotes', 'quote')
            ->andWhere('quote.status = :status')
            ->andWhere('client.company = :company')
            ->andWhere(self::IS_NOT_DELETED)
            ->setParameter('status', $status)
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all active clients associated with a specific company.
     *
     * @param Company $company The company for which to find active clients.
     * @return Client[] An array of active clients associated with the company.

     */
    public function findActiveClientsByCompany(Company $company): array
    {

        return $this->createQueryBuilder('client')
            ->andWhere(self::IS_NOT_DELETED)
            ->andWhere('client.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get the total number of clients associated with a company.
     *
     * @param Company $company The company for which to count clients.
     * @return int The total number of clients associated with the company.
     * @throws \Doctrine\ORM\NoResultException|\Doctrine\ORM\NonUniqueResultException
     */
    public function getTotalClientCountForCompany(Company $company): int
    {
        try {
            return $this->createQueryBuilder('client')
                ->select('COUNT(client.id)')
                ->andWhere('client.company = :company')
                ->andWhere(self::IS_NOT_DELETED)
                ->setParameter('company', $company)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException | \Doctrine\ORM\NonUniqueResultException $e) {
            return 0;
        }
    }

    public function getTotalClientsByMonth(Company $company): array
    {
        $query = $this->createQueryBuilder('client')
            ->select('EXTRACT(MONTH FROM client.createdAt) AS month_number, COUNT(client.id) AS total_clients')
            ->andWhere('client.company = :company')
            ->andWhere(self::IS_NOT_DELETED)
            ->groupBy('month_number')
            ->orderBy('month_number')
            ->setParameter('company', $company)
            ->getQuery();

        $results = $query->getResult();

        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
            7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        $clientsByMonth = [];
        foreach ($results as $result) {
            $monthName = $months[$result['month_number']];
            $clientsByMonth[$monthName] = $result['total_clients'];
        }

        return $clientsByMonth;
    }


    //    /**
    //     * @return Client[] Returns an array of Client objects
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

    //    public function findOneBySomeField($value): ?Client
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
