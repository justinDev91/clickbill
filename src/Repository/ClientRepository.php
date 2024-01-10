<?php

namespace App\Repository;

use App\Entity\Client;
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
     * Search clients by name or email
     *
     * @param string $value
     * @return Client
     */
    public function searchClientByNameOrEmail($term): ?array
    {
        return $this->createQueryBuilder('client')
            ->andWhere('client.name LIKE :searchTerm OR client.email LIKE :searchTerm')
            ->andWhere(self::IS_NOT_DELETED)
            ->setParameter('searchTerm', '%' . $term . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Filters clients by bill status
     *
     * @param string $status The status to filter by.
     * @return array|null An array of clients matching the specified status.
     */
    public function filterClientsByStatus($status): ?array
    {
        return $this->createQueryBuilder('client')
            ->join('client.bills', 'bill')
            ->andWhere('bill.status = :status')
            ->andWhere(self::IS_NOT_DELETED)
            ->setParameter('status', $status)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all active clients
     *
     * @return Client[]
     */
    public function findAllActiveClients(): array
    {
        return $this->createQueryBuilder('client')
            ->andWhere(self::IS_NOT_DELETED)
            ->getQuery()
            ->getResult();
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
