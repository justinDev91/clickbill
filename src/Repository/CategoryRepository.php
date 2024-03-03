<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    private const IS_NOT_DELETED = 'category.isDeleted = false';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Get all active categories
     * 
     * @return Category[] Array of all categories not deleted
     */
    public function getAllActiveCategories($company): array
    {
        return $this->createQueryBuilder('category')
            ->andWhere(self::IS_NOT_DELETED)
            ->andWhere('category.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get categories by name or description
     */
    public function searchCategoriesByNameOrDescription($term, $company): ?array
    {
        return $this->createQueryBuilder('category')
            ->andWhere(self::IS_NOT_DELETED)
            ->andWhere('category.name LIKE :searchTerm
            OR category.description LIKE :searchTerm')
            ->andWhere('category.company = :company')
            ->setParameter('searchTerm', '%' . $term . '%')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Category[] Returns an array of Category objects
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

//    public function findOneBySomeField($value): ?Category
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
